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
 * Чиббис — шаблон webhook заказа.
 *
 * Контракт (ожидаемое тело POST /api/ingress/chibbis/orders):
 * - orderId: string
 * - createdAt: ISO-8601
 * - client: { fullName, phoneNumber, email? }
 * - deliveryType: delivery|pickup
 * - address?: { street, building, entrance?, apartment?, comment? }
 * - paymentType: online|cash|card_courier
 * - products: [{ vendorCode, amount, price }]
 */
final class ChibbisIngressPartnerAdapter implements IngressPartnerAdapter
{
    public function partnerCode(): string
    {
        return 'chibbis';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function extractExternalOrderId(array $payload): string
    {
        return IngressAdapterSupport::requireString(
            $payload,
            'orderId',
            'Чиббис: orderId обязателен.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function map(array $payload): IngressMappedOrder
    {
        $externalOrderId = $this->extractExternalOrderId($payload);
        $client = IngressAdapterSupport::nestedArray($payload, 'client');
        $addressPayload = IngressAdapterSupport::nestedArray($payload, 'address');

        $clientName = IngressAdapterSupport::requireString(
            $client,
            'fullName',
            'Чиббис: client.fullName обязателен.',
        );
        $clientPhone = IngressAdapterSupport::requireString(
            $client,
            'phoneNumber',
            'Чиббис: client.phoneNumber обязателен.',
        );

        $deliveryType = strtolower((string) ($payload['deliveryType'] ?? 'delivery'));
        $deliveryMethod = $deliveryType === 'pickup'
            ? DeliveryMethod::Pickup
            : DeliveryMethod::Courier;

        $address = $addressPayload !== []
            ? new IngressMappedAddress(
                street: (string) ($addressPayload['street'] ?? ''),
                house: (string) ($addressPayload['building'] ?? $addressPayload['house'] ?? ''),
                entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
            )
            : null;

        $paymentType = strtolower((string) ($payload['paymentType'] ?? 'online'));
        $paymentMethod = match ($paymentType) {
            'cash' => PaymentMethod::Cash,
            'card_courier' => PaymentMethod::CardCourier,
            default => PaymentMethod::CardOnline,
        };

        $lines = $this->mapLines($payload);

        return new IngressMappedOrder(
            externalOrderId: $externalOrderId,
            placedAt: IngressAdapterSupport::parseDateTime($payload['createdAt'] ?? null),
            clientName: $clientName,
            clientPhone: $clientPhone,
            clientEmail: isset($client['email']) ? (string) $client['email'] : null,
            deliveryMethod: $deliveryMethod,
            deliveryAddress: $address,
            deliveryComment: isset($addressPayload['comment']) ? (string) $addressPayload['comment'] : null,
            deliveryScheduledAt: isset($payload['deliveryTime']) ? (string) $payload['deliveryTime'] : null,
            paymentMethod: $paymentMethod,
            paymentChangeFromRubles: isset($payload['changeFrom'])
                ? (int) $payload['changeFrom']
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
        $products = $payload['products'] ?? [];

        if (! is_array($products)) {
            throw IngressInvariantViolation::invalidPayload('Чиббис: products должен быть массивом.');
        }

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $partnerSku = trim((string) ($product['vendorCode'] ?? ''));
            $quantity = (int) ($product['amount'] ?? 0);
            $unitPriceRubles = IngressAdapterSupport::rublesFromMajorUnit($product['price'] ?? 0);

            if ($partnerSku === '' || $quantity < 1) {
                throw IngressInvariantViolation::invalidPayload('Чиббис: некорректная позиция products[].');
            }

            $lines[] = new IngressMappedLine(
                partnerSku: $partnerSku,
                quantity: $quantity,
                unitPriceRubles: $unitPriceRubles,
            );
        }

        if ($lines === []) {
            throw IngressInvariantViolation::invalidPayload('Чиббис: корзина пуста.');
        }

        return $lines;
    }
}
