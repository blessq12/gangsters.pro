<?php

namespace App\Application\OrderAccountingExport\Mapper;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\OrderAccountingExport\Exception\UnknownAccountingProductException;
use App\Domain\OrderAccountingExport\Repository\AccountingProductBindingRepository;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Enum\PaymentMethod;

/**
 * ACL: OrderCreated → тело запроса iiko Cloud /api/1/deliveries/create.
 */
final class IikoOrderMapper
{
    private const SYSTEM_CODE = 'iiko';

    public function __construct(
        private readonly AccountingProductBindingRepository $productBindings,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toRequest(OrderCreated $event): array
    {
        $organizationId = (string) config('order-accounting-export.systems.iiko.organization_id', '');
        $terminalGroupId = (string) config('order-accounting-export.systems.iiko.terminal_group_id', '');

        if ($organizationId === '' || $terminalGroupId === '') {
            throw new \InvalidArgumentException('Для iiko не заданы organization_id или terminal_group_id.');
        }

        $order = [
            'phone' => $this->formatPhone($event->client()->phone()),
            'comment' => $this->buildComment($event),
            'customer' => [
                'name' => (string) ($event->client()->name() ?? 'Клиент'),
                'type' => 'regular',
            ],
            'items' => $this->mapItems($event),
            'payments' => [$this->mapPayment($event)],
        ];

        if ($event->delivery()->method() === DeliveryMethod::Pickup) {
            $order['orderServiceType'] = 'DeliveryByClient';
        } else {
            $order['orderServiceType'] = 'DeliveryByCourier';
            $deliveryPoint = $this->mapDeliveryPoint($event);
            if ($deliveryPoint !== null) {
                $order['deliveryPoint'] = $deliveryPoint;
            }
        }

        $scheduledAt = $event->delivery()->scheduledAt();
        if (is_string($scheduledAt) && $scheduledAt !== '') {
            $order['completeBefore'] = $this->formatDateTime($scheduledAt);
        }

        return [
            'organizationId' => $organizationId,
            'terminalGroupId' => $terminalGroupId,
            'order' => $order,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapItems(OrderCreated $event): array
    {
        $items = [];

        foreach ($event->cart()->lines() as $line) {
            if ($line->isPromotionBenefitLine()) {
                continue;
            }

            $productId = $this->productBindings->resolveExternalProductId(self::SYSTEM_CODE, $line->productId());
            if ($productId === null || $productId === '') {
                throw new UnknownAccountingProductException(self::SYSTEM_CODE, $line->productId());
            }

            $items[] = [
                'productId' => $productId,
                'type' => 'Product',
                'amount' => $line->quantity(),
                'price' => $line->unitPrice()->amountRubles(),
            ];
        }

        if ($items === []) {
            throw new \InvalidArgumentException('Заказ не содержит позиций для экспорта в iiko.');
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPayment(OrderCreated $event): array
    {
        $method = $event->payment()->method();
        $map = config('order-accounting-export.systems.iiko.payment_types', []);
        $payment = is_array($map) ? ($map[$method->value] ?? null) : null;

        if (! is_array($payment)) {
            throw new \InvalidArgumentException(sprintf('Не настроен тип оплаты iiko для «%s».', $method->value));
        }

        $paymentTypeId = (string) ($payment['id'] ?? '');
        $paymentTypeKind = (string) ($payment['kind'] ?? '');

        if ($paymentTypeId === '' || $paymentTypeKind === '') {
            throw new \InvalidArgumentException(sprintf('Не настроен тип оплаты iiko для «%s».', $method->value));
        }

        return [
            'paymentTypeKind' => $paymentTypeKind,
            'paymentTypeId' => $paymentTypeId,
            'sum' => $event->cart()->payableTotal()->amountRubles(),
            'isProcessedExternally' => $method === PaymentMethod::CardOnline,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapDeliveryPoint(OrderCreated $event): ?array
    {
        $address = $event->delivery()->address();
        if ($address === null) {
            return null;
        }

        $streetId = (string) config('order-accounting-export.systems.iiko.default_street_id', '');
        $street = [
            'name' => trim($address->street()),
        ];

        if ($streetId !== '') {
            $street['id'] = $streetId;
        }

        $deliveryPoint = [
            'address' => [
                'street' => $street,
                'house' => trim($address->house()),
            ],
        ];

        if ($address->apartment() !== null && $address->apartment() !== '') {
            $deliveryPoint['address']['flat'] = trim($address->apartment());
        }

        $coordinates = $this->resolveCoordinates($event);
        if ($coordinates !== null) {
            $deliveryPoint['coordinates'] = $coordinates;
        }

        return $deliveryPoint;
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function resolveCoordinates(OrderCreated $event): ?array
    {
        $latitude = config('order-accounting-export.systems.iiko.default_latitude');
        $longitude = config('order-accounting-export.systems.iiko.default_longitude');

        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        return [
            'latitude' => (float) $latitude,
            'longitude' => (float) $longitude,
        ];
    }

    private function buildComment(OrderCreated $event): string
    {
        $parts = [
            sprintf('Заказ #%d', $event->orderId()->value()),
        ];

        $comment = $event->delivery()->comment();
        if (is_string($comment) && trim($comment) !== '') {
            $parts[] = trim($comment);
        }

        return implode('. ', $parts);
    }

    private function formatPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '8') && strlen($digits) === 11) {
            $digits = '7'.substr($digits, 1);
        }

        if (str_starts_with($digits, '7')) {
            return '+'.$digits;
        }

        return '+'.$digits;
    }

    private function formatDateTime(string $value): string
    {
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return $value;
        }

        return date('Y-m-d H:i:s.000', $timestamp);
    }
}
