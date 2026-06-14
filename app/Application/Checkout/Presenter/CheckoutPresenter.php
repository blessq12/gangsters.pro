<?php

namespace App\Application\Checkout\Presenter;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\DeliveryAddress;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;

final class CheckoutPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Checkout $checkout): array
    {
        return [
            'checkout_id' => $checkout->id()->value(),
            'status' => $checkout->status()->value,
            'cart' => $this->presentCart($checkout),
            'client' => $checkout->client() instanceof ClientSnapshot
                ? $this->presentClient($checkout->client())
                : null,
            'delivery' => $checkout->delivery() instanceof DeliverySnapshot
                ? $this->presentDelivery($checkout->delivery())
                : null,
            'payment' => $checkout->payment() instanceof PaymentSnapshot
                ? $this->presentPayment($checkout->payment())
                : null,
            'created_at' => $checkout->createdAt()->format(DATE_ATOM),
            'confirmed_at' => $checkout->confirmedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentCart(Checkout $checkout): array
    {
        return [
            'items' => array_map(
                fn (CartLineSnapshot $line): array => [
                    'product_id' => $line->productId(),
                    'product_name' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unit_price_rubles' => $line->unitPrice()->amountRubles(),
                    'line_total_rubles' => $line->lineTotal()->amountRubles(),
                    'payload' => $line->payload(),
                ],
                $checkout->cart()->lines(),
            ),
            'items_total_rubles' => $checkout->cart()->itemsTotal()->amountRubles(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentClient(ClientSnapshot $client): array
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
    private function presentDelivery(DeliverySnapshot $delivery): array
    {
        $address = $delivery->address();

        return [
            'method' => $delivery->method()->value,
            'address' => $address instanceof DeliveryAddress
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
    private function presentPayment(PaymentSnapshot $payment): array
    {
        return [
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
        ];
    }
}
