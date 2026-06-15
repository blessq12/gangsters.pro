<?php

namespace App\Application\OrderAccountingExport\Mapper;

use App\Domain\Order\Event\OrderCreated;

/**
 * ACL: доменное событие заказа → нейтральный payload для адаптеров учёта.
 */
final class OrderCreatedToExportPayloadMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function toPayload(OrderCreated $event): array
    {
        $aggregator = $event->aggregatorReference();

        return [
            'order_id' => $event->orderId()->value(),
            'source' => $event->source()->value,
            'checkout_id' => $event->checkoutId(),
            'partner_code' => $aggregator?->partnerCode(),
            'external_order_id' => $aggregator?->externalOrderId(),
            'occurred_at' => $event->occurredAt()->format(DATE_ATOM),
            'cart' => $event->cart(),
            'client' => $event->client(),
            'delivery' => $event->delivery(),
            'payment' => $event->payment(),
        ];
    }
}
