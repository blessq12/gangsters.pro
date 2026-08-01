<?php

namespace App\Infrastructure\Crm\Listener;

use App\Domain\Crm\Entity\OrderHistoryEntry;
use App\Domain\Crm\Repository\OrderHistoryRepository;
use App\Domain\Order\Event\OrderCreated;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * CRM: слепок заказа в историю клиента по событию OrderCreated.
 */
final class RecordOrderHistoryOnCreated
{
    public function __construct(
        private readonly OrderHistoryRepository $orderHistory,
    ) {}

    public function handle(OrderCreated $event): void
    {
        $clientId = $this->resolveClientId($event);
        if ($clientId === null) {
            return;
        }

        try {
            $entry = OrderHistoryEntry::record(
                clientId: $clientId,
                orderSnapshot: $this->buildSnapshot($event),
                placedAt: $event->occurredAt(),
            );

            $this->orderHistory->save($entry);
        } catch (Throwable $exception) {
            Log::error('CRM order history failed after OrderCreated', [
                'order_id' => $event->orderId(),
                'client_id' => $clientId,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function resolveClientId(OrderCreated $event): ?int
    {
        $clientId = $event->client()['client_id'] ?? null;
        if ($clientId === null) {
            return null;
        }

        $clientId = (int) $clientId;

        return $clientId > 0 ? $clientId : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSnapshot(OrderCreated $event): array
    {
        return [
            'id' => $event->orderId(),
            'source' => $event->source(),
            'checkout_id' => $event->checkoutId(),
            'partner_code' => $event->partnerCode(),
            'external_order_id' => $event->externalOrderId(),
            'total' => $this->resolveTotalRubles($event),
            'cart' => $event->cart(),
            'client' => $event->client(),
            'delivery' => $event->delivery(),
            'payment' => $event->payment(),
            'created_at' => $event->occurredAt()->format(DATE_ATOM),
        ];
    }

    private function resolveTotalRubles(OrderCreated $event): int
    {
        $total = 0;

        foreach ($event->cart()['lines'] ?? [] as $line) {
            if (! is_array($line)) {
                continue;
            }

            $kind = is_array($line['payload'] ?? null)
                ? (string) (($line['payload']['kind'] ?? '') ?: 'user')
                : 'user';

            if (in_array($kind, ['gift', 'complement'], true)) {
                continue;
            }

            $total += (int) ($line['line_total_rubles'] ?? 0);
        }

        return $total;
    }
}
