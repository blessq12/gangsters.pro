<?php

namespace App\Application\Order\Presenter;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;

final class OrderPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        return [
            'id' => $order->id()->value(),
            'source' => $order->source()->value,
            'client_request_id' => $order->clientRequestId(),
            'checkout_id' => $order->checkoutId(),
            'partner_code' => $order->aggregatorReference()?->partnerCode(),
            'external_order_id' => $order->aggregatorReference()?->externalOrderId(),
            'status' => $order->status()->value,
            'total' => $order->cart()->itemsTotal()->amountRubles(),
            'created_at' => $order->createdAt()->format(DATE_ATOM),
            'client' => $this->presentClient($order->client()),
            'delivery' => $this->presentDelivery($order->delivery()),
            'payment' => $this->presentPayment($order->payment()),
            'items' => array_map(
                fn (OrderLineSnapshot $line): array => $this->presentLine($line),
                $order->cart()->lines(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentClient(OrderClientSnapshot $client): array
    {
        return [
            'kind' => $client->kind()->value,
            'client_id' => $client->clientId(),
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentDelivery(OrderDeliverySnapshot $delivery): array
    {
        $address = $delivery->address();

        return [
            'method' => $delivery->method()->value,
            'address' => $address instanceof OrderDeliveryAddress
                ? [
                    'street' => $address->street(),
                    'house' => $address->house(),
                    'entrance' => $address->entrance(),
                    'apartment' => $address->apartment(),
                ]
                : null,
            'comment' => $delivery->comment(),
            'scheduled_at' => $delivery->scheduledAt(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPayment(OrderPaymentSnapshot $payment): array
    {
        return [
            'method' => $this->presentPaymentMethodForFrontend($payment->method()->value),
            'change_from_rubles' => $payment->changeFromRubles(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentLine(OrderLineSnapshot $line): array
    {
        return [
            'id' => $line->productId(),
            'quantity' => $line->quantity(),
            'row_total' => $line->lineTotal()->amountRubles(),
            'kind' => $line->lineKind(),
            'product' => [
                'name' => $line->productName(),
            ],
        ];
    }

    private function presentPaymentMethodForFrontend(string $method): string
    {
        return match ($method) {
            'card_courier', 'card_online' => 'card',
            default => $method,
        };
    }
}
