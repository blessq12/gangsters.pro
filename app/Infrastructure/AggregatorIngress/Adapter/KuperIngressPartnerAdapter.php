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
 * Купер — шаблон webhook заказа.
 *
 * Контракт (ожидаемое тело POST /api/ingress/kuper/orders):
 * - order: { uuid, created_at }
 * - user: { name, phone, email? }
 * - shipment: { type: courier|pickup, address?: { street, house, entrance?, apartment?, doorphone? }, comment?, scheduled_at? }
 * - payment: { method: prepaid|cash|card_courier }
 * - positions: [{ id: partner_sku, quantity, price_kopecks }]
 */
final class KuperIngressPartnerAdapter implements IngressPartnerAdapter
{
    public function partnerCode(): string
    {
        return 'kuper';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function extractExternalOrderId(array $payload): string
    {
        $order = IngressAdapterSupport::nestedArray($payload, 'order');

        return IngressAdapterSupport::requireString(
            $order,
            'uuid',
            'Купер: order.uuid обязателен.',
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function map(array $payload): IngressMappedOrder
    {
        $order = IngressAdapterSupport::nestedArray($payload, 'order');
        $externalOrderId = $this->extractExternalOrderId($payload);
        $user = IngressAdapterSupport::nestedArray($payload, 'user');
        $shipment = IngressAdapterSupport::nestedArray($payload, 'shipment');
        $payment = IngressAdapterSupport::nestedArray($payload, 'payment');

        $clientName = IngressAdapterSupport::requireString(
            $user,
            'name',
            'Купер: user.name обязателен.',
        );
        $clientPhone = IngressAdapterSupport::requireString(
            $user,
            'phone',
            'Купер: user.phone обязателен.',
        );

        $shipmentType = strtolower((string) ($shipment['type'] ?? 'courier'));
        $deliveryMethod = $shipmentType === 'pickup'
            ? DeliveryMethod::Pickup
            : DeliveryMethod::Courier;

        $addressPayload = IngressAdapterSupport::nestedArray($shipment, 'address');
        $address = $addressPayload !== []
            ? new IngressMappedAddress(
                street: (string) ($addressPayload['street'] ?? ''),
                house: (string) ($addressPayload['house'] ?? ''),
                entrance: isset($addressPayload['entrance'])
                    ? (string) $addressPayload['entrance']
                    : (isset($addressPayload['doorphone']) ? (string) $addressPayload['doorphone'] : null),
                apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
            )
            : null;

        $paymentMethodRaw = strtolower((string) ($payment['method'] ?? 'prepaid'));
        $paymentMethod = match ($paymentMethodRaw) {
            'cash' => PaymentMethod::Cash,
            'card_courier' => PaymentMethod::CardCourier,
            default => PaymentMethod::CardOnline,
        };

        $lines = $this->mapLines($payload);

        return new IngressMappedOrder(
            externalOrderId: $externalOrderId,
            placedAt: IngressAdapterSupport::parseDateTime($order['created_at'] ?? null),
            clientName: $clientName,
            clientPhone: IngressAdapterSupport::normalizeClientPhone($clientPhone),
            clientEmail: isset($user['email']) ? (string) $user['email'] : null,
            deliveryMethod: $deliveryMethod,
            deliveryAddress: $address,
            deliveryComment: isset($shipment['comment']) ? (string) $shipment['comment'] : null,
            deliveryScheduledAt: isset($shipment['scheduled_at']) ? (string) $shipment['scheduled_at'] : null,
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
        $positions = $payload['positions'] ?? [];

        if (! is_array($positions)) {
            throw IngressInvariantViolation::invalidPayload('Купер: positions должен быть массивом.');
        }

        foreach ($positions as $position) {
            if (! is_array($position)) {
                continue;
            }

            $partnerSku = trim((string) ($position['id'] ?? ''));
            $quantity = (int) ($position['quantity'] ?? 0);
            $unitPriceRubles = isset($position['price_kopecks'])
                ? IngressAdapterSupport::rublesFromKopecks((int) $position['price_kopecks'])
                : IngressAdapterSupport::rublesFromMajorUnit($position['price_rubles'] ?? 0);

            if ($partnerSku === '' || $quantity < 1) {
                throw IngressInvariantViolation::invalidPayload('Купер: некорректная позиция positions[].');
            }

            $lines[] = new IngressMappedLine(
                partnerSku: $partnerSku,
                quantity: $quantity,
                unitPriceRubles: $unitPriceRubles,
            );
        }

        if ($lines === []) {
            throw IngressInvariantViolation::invalidPayload('Купер: корзина пуста.');
        }

        return $lines;
    }
}
