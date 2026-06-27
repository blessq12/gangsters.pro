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
 * Заглушка партнёра для e2e и разработки адаптеров.
 */
final class StubIngressPartnerAdapter implements IngressPartnerAdapter
{
    public function partnerCode(): string
    {
        return 'stub';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function extractExternalOrderId(array $payload): string
    {
        return IngressAdapterSupport::requireString(
            $payload,
            'external_order_id',
            'Поле external_order_id обязательно.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function map(array $payload): IngressMappedOrder
    {
        $externalOrderId = $this->extractExternalOrderId($payload);

        $client = is_array($payload['client'] ?? null) ? $payload['client'] : [];
        $clientName = trim((string) ($client['name'] ?? ''));
        $clientPhone = trim((string) ($client['phone'] ?? ''));
        if ($clientName === '' || $clientPhone === '') {
            throw IngressInvariantViolation::invalidPayload('Клиент: name и phone обязательны.');
        }

        $delivery = is_array($payload['delivery'] ?? null) ? $payload['delivery'] : [];
        $deliveryMethod = DeliveryMethod::tryFrom((string) ($delivery['method'] ?? ''))
            ?? DeliveryMethod::Courier;

        $payment = is_array($payload['payment'] ?? null) ? $payload['payment'] : [];
        $paymentMethod = PaymentMethod::tryFrom((string) ($payment['method'] ?? ''))
            ?? PaymentMethod::CardOnline;

        $placedAtRaw = (string) ($payload['placed_at'] ?? 'now');
        $placedAt = IngressAdapterSupport::parseDateTime($placedAtRaw);

        $lines = [];
        foreach ($payload['lines'] ?? [] as $linePayload) {
            if (! is_array($linePayload)) {
                continue;
            }

            $partnerSku = trim((string) ($linePayload['partner_sku'] ?? ''));
            $quantity = (int) ($linePayload['quantity'] ?? 0);
            $unitPriceRubles = (int) ($linePayload['unit_price_rubles'] ?? 0);

            if ($partnerSku === '' || $quantity < 1 || $unitPriceRubles < 0) {
                throw IngressInvariantViolation::invalidPayload('Некорректная строка заказа.');
            }

            $lines[] = new IngressMappedLine(
                partnerSku: $partnerSku,
                quantity: $quantity,
                unitPriceRubles: $unitPriceRubles,
            );
        }

        if ($lines === []) {
            throw IngressInvariantViolation::invalidPayload('Корзина не может быть пустой.');
        }

        $addressPayload = is_array($delivery['address'] ?? null) ? $delivery['address'] : null;
        $address = null;
        if ($addressPayload !== null) {
            $address = new IngressMappedAddress(
                street: (string) ($addressPayload['street'] ?? ''),
                house: (string) ($addressPayload['house'] ?? ''),
                entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
            );
        }

        return new IngressMappedOrder(
            externalOrderId: $externalOrderId,
            placedAt: $placedAt,
            clientName: $clientName,
            clientPhone: IngressAdapterSupport::normalizeClientPhone($clientPhone),
            clientEmail: isset($client['email']) ? (string) $client['email'] : null,
            deliveryMethod: $deliveryMethod,
            deliveryAddress: $address,
            deliveryComment: isset($delivery['comment']) ? (string) $delivery['comment'] : null,
            deliveryScheduledAt: isset($delivery['scheduled_at']) ? (string) $delivery['scheduled_at'] : null,
            paymentMethod: $paymentMethod,
            paymentChangeFromRubles: isset($payment['change_from_rubles'])
                ? (int) $payment['change_from_rubles']
                : null,
            lines: $lines,
        );
    }
}
