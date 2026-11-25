<?php

namespace App\Infrastructure\Order\Mapper;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Order\Model\OrderLineModel;
use App\Shared\Enum\DeliveryMethod;
use DateTimeImmutable;

/**
 * Order ↔ ORD_orders + ORD_order_lines. Агрегат целиком, деньги в копейках.
 */
final class OrderMapper
{
    public function toDomain(OrderModel $row): Order
    {
        $lines = [];
        $lineRows = $row->relationLoaded('lines')
            ? $row->lines
            : $row->lines()->get();

        foreach ($lineRows as $lineRow) {
            if (! $lineRow instanceof OrderLineModel) {
                continue;
            }

            $lines[] = new OrderLine(
                productId: (int) $lineRow->product_id,
                name: (string) $lineRow->name,
                unitPriceKopecks: (int) $lineRow->unit_price_kopecks,
                quantity: (int) $lineRow->quantity,
                lineType: OrderLineType::from((string) $lineRow->line_type),
            );
        }

        $createdAt = $row->created_at !== null
            ? DateTimeImmutable::createFromInterface($row->created_at)
            : new DateTimeImmutable;

        return Order::restore(
            id: (int) $row->id,
            source: (string) ($row->source ?? 'site'),
            checkoutId: $this->nullableString($row->checkout_id),
            partnerCode: null,
            externalOrderId: null,
            status: (string) $row->status,
            lines: $lines,
            client: new OrderClientSnapshot(
                clientId: $row->client_id !== null ? (int) $row->client_id : null,
                name: (string) $row->client_name,
                phone: (string) $row->client_phone,
            ),
            delivery: $this->mapDelivery($row),
            createdAt: $createdAt,
            branchId: $row->branch_id !== null ? (int) $row->branch_id : null,
            paymentId: $row->payment_id !== null ? (int) $row->payment_id : null,
            paymentMethod: $this->nullableString($row->payment_method),
            changeFromRubles: $row->change_from_rubles !== null ? (int) $row->change_from_rubles : null,
            scheduledAt: $row->scheduled_at !== null
                ? DateTimeImmutable::createFromInterface($row->scheduled_at)
                : null,
        );
    }

    /**
     * @return array{order: array<string, mixed>, lines: list<array<string, mixed>>}
     */
    public function toPersistence(Order $order): array
    {
        $client = $order->client();
        $delivery = $order->delivery();
        $address = $delivery->address();

        return [
            'order' => [
                'id' => $order->hasId() ? $order->id() : null,
                'source' => $order->source(),
                'checkout_id' => $order->checkoutId(),
                'status' => $order->status()->value,
                'client_id' => $client->clientId(),
                'client_name' => $client->name(),
                'client_phone' => $client->phone(),
                'delivery_type' => $delivery->type()->value,
                'delivery_zone_id' => $delivery->zoneId(),
                'delivery_fee_kopecks' => $delivery->feeKopecks(),
                'delivery_street' => $address?->street(),
                'delivery_house' => $address?->house(),
                'delivery_entrance' => $address?->entrance(),
                'delivery_floor' => $address?->floor(),
                'delivery_apartment' => $address?->apartment(),
                'delivery_comment' => $address?->comment(),
                'delivery_lat' => $address?->lat(),
                'delivery_lng' => $address?->lng(),
                'branch_id' => $order->branchId(),
                'payment_id' => $order->paymentId(),
                'payment_method' => $order->paymentMethod(),
                'change_from_rubles' => $order->changeFromRubles(),
                'total_kopecks' => $order->grandTotalKopecks(),
                'scheduled_at' => $order->scheduledAt()?->format('Y-m-d H:i:s'),
                'created_at' => $order->createdAt()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'lines' => $this->mapLines($order),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapLines(Order $order): array
    {
        $lines = [];
        foreach (array_values($order->lines()) as $index => $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'unit_price_kopecks' => $line->unitPriceKopecks(),
                'quantity' => $line->quantity(),
                'line_type' => $line->lineType()->value,
                'position' => $index,
            ];
        }

        return $lines;
    }

    private function mapDelivery(OrderModel $row): OrderDeliveryInfo
    {
        $type = DeliveryMethod::from((string) $row->delivery_type);

        $address = null;
        if ($type === DeliveryMethod::Courier) {
            $address = OrderDeliveryAddress::restore(
                street: (string) $row->delivery_street,
                house: (string) $row->delivery_house,
                entrance: $this->nullableString($row->delivery_entrance),
                floor: $this->nullableString($row->delivery_floor),
                apartment: $this->nullableString($row->delivery_apartment),
                comment: $this->nullableString($row->delivery_comment),
                lat: $this->nullableFloat($row->delivery_lat),
                lng: $this->nullableFloat($row->delivery_lng),
            );
        }

        return OrderDeliveryInfo::restore(
            type: $type,
            address: $address,
            zoneId: $this->nullableString($row->delivery_zone_id),
            feeKopecks: (int) $row->delivery_fee_kopecks,
        );
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = (float) $value;

        return is_finite($number) ? $number : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }
}
