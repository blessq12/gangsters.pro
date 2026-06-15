<?php

namespace App\Infrastructure\AggregatorIngress\Adapter;

use App\Application\AggregatorIngress\Port\IngressPartnerAdapter;
use App\Domain\AggregatorIngress\Exception\IngressInvariantViolation;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedAddress;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedLine;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedOrder;
use App\Infrastructure\AggregatorIngress\Support\IngressAdapterSupport;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;

/**
 * Яндекс Еда — шаблон webhook заказа.
 *
 * Контракт (ожидаемое тело POST /api/ingress/yandex-eda/orders):
 * - order_id: string
 * - created_at: ISO-8601
 * - customer: { name, phone, email? }
 * - delivery: { type: courier|pickup, address?: { street, house, entrance?, apartment?, comment? }, scheduled_at? }
 * - payment: { type: card_online|card_courier|cash }
 * - items: [{ id: partner_sku, quantity, price_rubles }]
 */
final class YandexEdaIngressPartnerAdapter implements IngressPartnerAdapter
{
    public function partnerCode(): string
    {
        return 'yandex-eda';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function extractExternalOrderId(array $payload): string
    {
        return IngressAdapterSupport::requireString(
            $payload,
            'order_id',
            'Яндекс Еда: order_id обязателен.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function map(array $payload): IngressMappedOrder
    {
        $externalOrderId = $this->extractExternalOrderId($payload);
        $customer = IngressAdapterSupport::nestedArray($payload, 'customer');
        $delivery = IngressAdapterSupport::nestedArray($payload, 'delivery');
        $payment = IngressAdapterSupport::nestedArray($payload, 'payment');

        $clientName = IngressAdapterSupport::requireString(
            $customer,
            'name',
            'Яндекс Еда: customer.name обязателен.',
        );
        $clientPhone = IngressAdapterSupport::requireString(
            $customer,
            'phone',
            'Яндекс Еда: customer.phone обязателен.',
        );

        $deliveryType = strtolower((string) ($delivery['type'] ?? 'courier'));
        $deliveryMethod = $deliveryType === 'pickup'
            ? DeliveryMethod::Pickup
            : DeliveryMethod::Courier;

        $addressPayload = IngressAdapterSupport::nestedArray($delivery, 'address');
        $address = $addressPayload !== []
            ? new IngressMappedAddress(
                street: (string) ($addressPayload['street'] ?? ''),
                house: (string) ($addressPayload['house'] ?? ''),
                entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
            )
            : null;

        $paymentType = strtolower((string) ($payment['type'] ?? 'card_online'));
        $paymentMethod = match ($paymentType) {
            'cash' => PaymentMethod::Cash,
            'card_courier' => PaymentMethod::CardCourier,
            default => PaymentMethod::CardOnline,
        };

        $lines = $this->mapLines($payload);

        return new IngressMappedOrder(
            externalOrderId: $externalOrderId,
            placedAt: IngressAdapterSupport::parseDateTime($payload['created_at'] ?? null),
            clientName: $clientName,
            clientPhone: $clientPhone,
            clientEmail: isset($customer['email']) ? (string) $customer['email'] : null,
            deliveryMethod: $deliveryMethod,
            deliveryAddress: $address,
            deliveryComment: isset($addressPayload['comment'])
                ? (string) $addressPayload['comment']
                : (isset($delivery['comment']) ? (string) $delivery['comment'] : null),
            deliveryScheduledAt: isset($delivery['scheduled_at']) ? (string) $delivery['scheduled_at'] : null,
            paymentMethod: $paymentMethod,
            paymentChangeFromRubles: isset($payment['change_from_rubles'])
                ? (int) $payment['change_from_rubles']
                : null,
            lines: $lines,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<IngressMappedLine>
     */
    private function mapLines(array $payload): array
    {
        $lines = [];
        $items = $payload['items'] ?? [];

        if (! is_array($items)) {
            throw IngressInvariantViolation::invalidPayload('Яндекс Еда: items должен быть массивом.');
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $partnerSku = trim((string) ($item['id'] ?? ''));
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitPriceRubles = IngressAdapterSupport::rublesFromMajorUnit($item['price_rubles'] ?? 0);

            if ($partnerSku === '' || $quantity < 1) {
                throw IngressInvariantViolation::invalidPayload('Яндекс Еда: некорректная позиция items[].');
            }

            $lines[] = new IngressMappedLine(
                partnerSku: $partnerSku,
                quantity: $quantity,
                unitPriceRubles: $unitPriceRubles,
            );
        }

        if ($lines === []) {
            throw IngressInvariantViolation::invalidPayload('Яндекс Еда: корзина пуста.');
        }

        return $lines;
    }
}
