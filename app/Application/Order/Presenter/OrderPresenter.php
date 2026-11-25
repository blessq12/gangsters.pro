<?php

namespace App\Application\Order\Presenter;

use App\Domain\Core\Order\Entity\Order;
use App\Domain\Core\Order\Port\OrderPaymentSnapshotPort;
use App\Domain\Core\Order\ValueObject\OrderNumber;

final class OrderPresenter
{
    private const KOPECKS_IN_RUBLE = 100;

    public function __construct(
        private readonly OrderPaymentSnapshotPort $payments,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $lines = [];
        foreach ($order->lines() as $line) {
            $lines[] = [
                'product_id' => $line->productId(),
                'name' => $line->name(),
                'unit_price_kopecks' => $line->unitPriceKopecks(),
                'quantity' => $line->quantity(),
                'line_type' => $line->lineType()->value,
                'line_total_kopecks' => $line->lineTotalKopecks(),
            ];
        }

        $subtotal = $order->subtotalKopecks();
        $deliveryFee = $order->delivery()->feeKopecks();
        $grand = $order->grandTotalKopecks();

        $totals = [
            'items_total' => $subtotal,
            'discount' => 0,
            'delivery_fee' => $deliveryFee,
            'grand_total' => $grand,
            'items_total_rubles' => intdiv($subtotal, self::KOPECKS_IN_RUBLE),
            'delivery_fee_rubles' => intdiv($deliveryFee, self::KOPECKS_IN_RUBLE),
            'grand_total_rubles' => intdiv($grand, self::KOPECKS_IN_RUBLE),
        ];

        $client = $order->client();
        $delivery = $order->delivery();
        $address = $delivery->address();

        $id = $order->hasId() ? $order->id() : null;

        return [
            'id' => $id,
            'number' => $id !== null ? OrderNumber::format($id, $order->createdAt()) : null,
            'status' => $order->status()->value,
            'branch_id' => $order->branchId(),
            'cart' => [
                'lines' => $lines,
                'totals' => $totals,
            ],
            'client' => [
                'id' => $client->clientId(),
                'name' => $client->name(),
                'phone' => $client->phone(),
            ],
            'delivery' => [
                'method' => $delivery->type()->value,
                'zone_id' => $delivery->zoneId(),
                'fee_kopecks' => $delivery->feeKopecks(),
                'address' => $address === null ? null : [
                    'street' => $address->street(),
                    'house' => $address->house(),
                    'entrance' => $address->entrance(),
                    'floor' => $address->floor(),
                    'apartment' => $address->apartment(),
                    'comment' => $address->comment(),
                    'lat' => $address->lat(),
                    'lng' => $address->lng(),
                ],
            ],
            'payment_id' => $order->paymentId(),
            'payment_method' => $order->paymentMethod(),
            'change_from_rubles' => $order->changeFromRubles(),
            'payment' => $this->payment($order),
            'totals' => $totals,
            'scheduled_at' => $order->scheduledAt()?->format(DATE_ATOM),
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'client_request_id' => $order->clientRequestId(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function payment(Order $order): ?array
    {
        $paymentId = $order->paymentId();
        if ($paymentId === null) {
            return null;
        }

        $snapshot = $this->payments->findByPaymentId($paymentId);
        if ($snapshot === null) {
            return null;
        }

        return [
            'id' => $snapshot['id'],
            'status' => $snapshot['status'],
            'method' => $snapshot['method'],
            'amount_kopecks' => $snapshot['amount_kopecks'],
            'change_from_rubles' => $snapshot['change_from_rubles'],
        ];
    }
}
