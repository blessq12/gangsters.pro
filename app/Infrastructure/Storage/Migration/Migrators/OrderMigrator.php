<?php

namespace App\Infrastructure\Storage\Migration\Migrators;

use App\Domain\Payment\Enum\PaymentStatus;
use App\Infrastructure\Storage\Migration\Contract\BoundedContextMigrator;
use App\Infrastructure\Storage\Migration\MigrationReport;
use App\Infrastructure\Storage\Migration\Support\ConnectionSchema;
use App\Shared\Enum\PaymentMethod;
use Illuminate\Database\ConnectionInterface;

final class OrderMigrator implements BoundedContextMigrator
{
    private const KOPECKS_IN_RUBLE = 100;

    public function key(): string
    {
        return 'order';
    }

    public function migrate(
        ConnectionInterface $source,
        ConnectionInterface $target,
        bool $dryRun,
        bool $truncateTarget,
    ): MigrationReport {
        $report = new MigrationReport();

        if (! ConnectionSchema::hasTable($source, 'ORD_orders')) {
            $report->skip('order: нет ORD_orders на source');

            return $report;
        }

        if ($source->getName() === $target->getName()
            && $source->getDatabaseName() === $target->getDatabaseName()
        ) {
            $hasOld = ConnectionSchema::hasColumn($source, 'ORD_orders', 'total_rubles');
            $hasNew = ConnectionSchema::hasColumn($target, 'ORD_orders', 'total_kopecks');
            if ($hasNew && ! $hasOld) {
                $report->skip('order: source=target и ORD_orders уже в новой схеме — нужен отдельный --source-database со старыми данными');

                return $report;
            }
        }

        if ($truncateTarget && ! $dryRun) {
            ConnectionSchema::truncate($target, ['ORD_orders', 'PAY_payments']);
        }

        $rows = $source->table('ORD_orders')->orderBy('id')->get();
        $report->addRead('ORD_orders', $rows->count());
        if ($dryRun) {
            $report->note('order: total_rubles → total_kopecks (*100)');

            return $report;
        }

        $payments = 0;
        foreach ($rows as $row) {
            $totalKopecks = null;
            if (isset($row->total_kopecks)) {
                $totalKopecks = (int) $row->total_kopecks;
            } elseif (isset($row->total_rubles)) {
                $totalKopecks = max(0, (int) $row->total_rubles) * self::KOPECKS_IN_RUBLE;
            }

            $cart = $row->cart_snapshot;
            $client = $row->client_snapshot;
            $delivery = $row->delivery_snapshot;
            $payment = $row->payment_snapshot ?? null;
            if (is_string($payment)) {
                $payment = json_decode($payment, true);
            }
            if (is_array($cart)) {
                $cart = json_encode($cart, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($client)) {
                $client = json_encode($client, JSON_UNESCAPED_UNICODE);
            }
            if (is_array($delivery)) {
                $delivery = json_encode($delivery, JSON_UNESCAPED_UNICODE);
            }

            $target->table('ORD_orders')->updateOrInsert(
                ['id' => $row->id],
                [
                    'source' => $row->source ?? 'site',
                    'checkout_id' => $row->checkout_id ?? null,
                    'partner_code' => $row->partner_code ?? null,
                    'external_order_id' => $row->external_order_id ?? null,
                    'status' => $row->status,
                    'cart_snapshot' => $cart,
                    'client_snapshot' => $client,
                    'delivery_snapshot' => $delivery,
                    'client_id' => $row->client_id ?? null,
                    'total_kopecks' => $totalKopecks,
                    'created_at' => $row->created_at ?? now(),
                ],
            );

            $method = is_array($payment) ? PaymentMethod::tryFrom((string) ($payment['method'] ?? '')) : null;
            if ($method !== null) {
                $target->table('PAY_payments')->updateOrInsert(
                    ['order_id' => $row->id],
                    [
                        'method' => $method->value,
                        'change_from_rubles' => isset($payment['change_from_rubles'])
                            ? (int) $payment['change_from_rubles']
                            : null,
                        'amount_kopecks' => $totalKopecks ?? 0,
                        'status' => PaymentStatus::Pending->value,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => now(),
                    ],
                );
                $paymentId = $target->table('PAY_payments')->where('order_id', $row->id)->value('id');
                $target->table('ORD_orders')->where('id', $row->id)->update(['payment_id' => $paymentId]);
                $payments++;
            }
        }
        $report->addWritten('ORD_orders', $rows->count());
        $report->addWritten('PAY_payments', $payments);

        return $report;
    }
}
