<?php

namespace App\Application\Order\Presenter;

use App\Application\Payment\Presenter\PaymentPresenter;
use App\Domain\Order\Entity\Order;
use App\Domain\Payment\Repository\PaymentRepository;

final class OrderPresenter
{
    public function __construct(
        private readonly PaymentRepository $payments,
        private readonly PaymentPresenter $paymentPresenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $cart = $order->cart();
        $totals = is_array($cart['totals'] ?? null)
            ? $cart['totals']
            : [
                'items_total' => 0,
                'discount' => 0,
                'delivery_fee' => 0,
                'grand_total' => $order->totalRubles() * 100,
                'grand_total_rubles' => $order->totalRubles(),
            ];

        return [
            'id' => $order->hasId() ? $order->id() : null,
            'status' => $order->status()->value,
            'kitchen_id' => $order->kitchenId(),
            'branch_id' => $order->branchId(),
            'cart' => $cart,
            'client' => $order->client(),
            'delivery' => $order->delivery(),
            'payment_id' => $order->paymentId(),
            'payment' => $this->payment($order),
            'totals' => $totals,
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

        $payment = $this->payments->findById($paymentId);

        return $payment !== null ? $this->paymentPresenter->present($payment) : null;
    }
}
