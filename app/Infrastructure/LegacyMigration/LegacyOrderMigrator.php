<?php

namespace App\Infrastructure\LegacyMigration;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderItem;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Infrastructure\Reporting\Model\ReportingClientOrderFact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LegacyOrderMigrator
{
    public function __construct(
        private readonly LegacyPhoneNormalizer $phoneNormalizer,
        private readonly LegacyOrderStatusMapper $statusMapper,
        private readonly LegacyMigrationMapRepository $maps,
    ) {}

    /**
     * @return array{migrated: int, skipped: int, items: int}
     */
    public function migrate(bool $dryRun = false): array
    {
        if (! Schema::hasTable('orders') || ! Schema::hasTable('ORD_orders')) {
            return ['migrated' => 0, 'skipped' => 0, 'items' => 0];
        }

        $stats = ['migrated' => 0, 'skipped' => 0, 'items' => 0];

        DB::table('orders')
            ->orderBy('id')
            ->chunk(100, function (Collection $orders) use ($dryRun, &$stats): void {
                foreach ($orders as $legacy) {
                    $legacyId = (string) $legacy->id;

                    if ($this->maps->findTargetKey(LegacyMigrationEntityType::LEGACY_ORDER, $legacyId) !== null) {
                        $stats['skipped']++;

                        continue;
                    }

                    $orderId = (string) Str::uuid();
                    $items = $this->loadLegacyItems((int) $legacy->id);
                    $payload = $this->buildOrderPayload($legacy, $items, $orderId);

                    foreach ($items as &$itemRow) {
                        $itemRow['order_id'] = $orderId;
                    }
                    unset($itemRow);

                    if ($dryRun) {
                        $stats['migrated']++;
                        $stats['items'] += count($items);

                        continue;
                    }

                    DB::transaction(function () use ($legacy, $legacyId, $payload, $items, $orderId, &$stats): void {
                        ORD_Order::query()->create($payload);

                        foreach ($items as $itemRow) {
                            ORD_OrderItem::query()->create($itemRow);
                        }

                        $this->maps->remember(
                            LegacyMigrationEntityType::LEGACY_ORDER,
                            $legacyId,
                            $orderId,
                            [
                                'frontpad_id' => $legacy->frontpad_id,
                                'eats_id' => $legacy->eatsId,
                            ],
                        );

                        $this->upsertReportingFact($payload, $orderId);

                        $stats['migrated']++;
                        $stats['items'] += count($items);
                    });
                }
            });

        return $stats;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadLegacyItems(int $legacyOrderId): array
    {
        if (! Schema::hasTable('order_items')) {
            return [];
        }

        $rows = DB::table('order_items')
            ->where('order_id', $legacyOrderId)
            ->orderBy('id')
            ->get();

        $items = [];
        foreach ($rows as $index => $row) {
            $productId = is_numeric($row->product_id) ? (int) $row->product_id : null;
            $product = $productId !== null
                ? PRD_Product::query()->find($productId)
                : null;

            $qty = max(1, (int) preg_replace('/\D+/', '', (string) $row->qty) ?: 1);
            $unitKopecks = $this->decimalRubToKopecks($row->price);
            $rowSubtotal = $unitKopecks * $qty;

            $items[] = [
                'order_id' => null,
                'product_original_id' => $product?->id,
                'product_name' => $product?->name ?? ('Товар '.($row->sku ?: $row->product_id)),
                'product_sku' => (string) ($product?->sku ?? $row->sku ?? ''),
                'product_list_price' => $unitKopecks,
                'product_final_price' => $unitKopecks,
                'product_attributes' => null,
                'product_media' => null,
                'quantity' => $qty,
                'unit_price' => $unitKopecks,
                'row_subtotal' => $rowSubtotal,
                'row_discount' => 0,
                'row_total' => $rowSubtotal,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        }

        return $items;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function buildOrderPayload(object $legacy, array $items, string $orderId): array
    {
        $phone = $this->phoneNormalizer->normalize($legacy->tel);
        $clientId = $this->resolveClientId($legacy, $phone);

        $subtotalFromItems = array_sum(array_column($items, 'row_subtotal'));
        $subtotal = $subtotalFromItems > 0
            ? $subtotalFromItems
            : $this->decimalRubToKopecks($legacy->itemsCost ?? $legacy->total);
        $total = $this->decimalRubToKopecks($legacy->total);
        if ($total <= 0) {
            $total = $subtotal;
        }
        $discount = max(0, $subtotal - $total);

        $address = $this->buildAddressPayload($legacy);
        $paymentMethod = $this->mapPaymentMethod((string) ($legacy->payType ?? 'cash'));
        $status = $this->statusMapper->map((int) $legacy->status);

        return [
            'id' => $orderId,
            'client_id' => $clientId,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'total' => $total,
            'customer_name' => $this->nonEmpty((string) ($legacy->name ?? ''), 'Гость'),
            'customer_phone' => $phone ?? '',
            'customer_email' => null,
            'customer_address' => $address,
            'delivery_method' => $this->mapDeliveryMethod($legacy),
            'delivery_address' => $address,
            'delivery_comment' => $legacy->comment,
            'payment_method' => $paymentMethod,
            'payment_external_id' => $legacy->frontpad_id ?? $legacy->eatsId,
            'payment_status' => $this->mapPaymentStatus($status, $paymentMethod),
            'created_at' => $legacy->created_at,
            'updated_at' => $legacy->updated_at,
        ];
    }

    private function resolveClientId(object $legacy, ?string $phone): ?int
    {
        if ($legacy->user_id !== null) {
            $mapped = $this->maps->findTargetKey(
                LegacyMigrationEntityType::LEGACY_USER,
                (string) $legacy->user_id,
            );
            if ($mapped !== null) {
                return (int) $mapped;
            }
        }

        if ($phone === null) {
            return null;
        }

        return UR_Client::query()->where('phone', $phone)->value('id');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildAddressPayload(object $legacy): ?array
    {
        $parts = array_filter([
            'street' => $legacy->street,
            'house' => $legacy->house,
            'entrance' => $legacy->staircase,
            'floor' => $legacy->floor,
            'apartment' => $legacy->apartment,
        ], fn ($value): bool => $value !== null && trim((string) $value) !== '');

        if ($parts === [] && ! empty($legacy->full_address)) {
            return ['formatted' => (string) $legacy->full_address];
        }

        return $parts === [] ? null : $parts;
    }

    private function mapDeliveryMethod(object $legacy): string
    {
        if (! (bool) ($legacy->delivery ?? false)) {
            return DeliveryMethod::Pickup->value;
        }

        $type = strtolower((string) ($legacy->deliveryType ?? ''));

        return str_contains($type, 'pickup') || str_contains($type, 'самовывоз')
            ? DeliveryMethod::Pickup->value
            : DeliveryMethod::Courier->value;
    }

    private function mapPaymentMethod(string $payType): string
    {
        $normalized = strtolower(trim($payType));

        return match (true) {
            str_contains($normalized, 'card'), str_contains($normalized, 'карт') => PaymentMethod::Card->value,
            str_contains($normalized, 'transfer'), str_contains($normalized, 'перевод') => PaymentMethod::Transfer->value,
            default => PaymentMethod::Cash->value,
        };
    }

    private function mapPaymentStatus(string $orderStatus, string $paymentMethod): string
    {
        if ($orderStatus === 'delivered') {
            return PaymentStatus::Paid->value;
        }

        return PaymentMethod::tryFrom($paymentMethod) === PaymentMethod::Card
            ? PaymentStatus::Processing->value
            : PaymentStatus::Unpaid->value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertReportingFact(array $payload, string $orderId): void
    {
        if ($payload['client_id'] === null || ! Schema::hasTable('reporting_client_order_facts')) {
            return;
        }

        ReportingClientOrderFact::query()->updateOrCreate(
            ['order_id' => $orderId],
            [
                'client_id' => $payload['client_id'],
                'payment_status' => $payload['payment_status'],
                'total' => $payload['total'],
                'created_at' => $payload['created_at'] ?? now(),
                'updated_at' => $payload['updated_at'] ?? now(),
            ],
        );
    }

    private function decimalRubToKopecks(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round(((float) $value) * 100);
    }

    private function nonEmpty(string $value, string $fallback): string
    {
        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : $fallback;
    }
}
