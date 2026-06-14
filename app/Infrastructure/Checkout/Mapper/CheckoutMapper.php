<?php

namespace App\Infrastructure\Checkout\Mapper;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Enum\CheckoutStatus;
use App\Domain\Checkout\Enum\ClientKind;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Enum\PaymentMethod;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\CartSnapshot;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\DeliveryAddress;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use App\Domain\Checkout\ValueObject\GuestContact;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;
use App\Infrastructure\Checkout\Model\CHK_Checkout;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class CheckoutMapper
{
    public function toDomain(CHK_Checkout $row): Checkout
    {
        return Checkout::restore(
            id: CheckoutId::fromString((string) $row->id),
            status: CheckoutStatus::from((string) $row->status),
            cart: $this->mapCartSnapshot(is_array($row->cart_snapshot) ? $row->cart_snapshot : []),
            client: is_array($row->client_snapshot)
                ? $this->mapClientSnapshot($row->client_snapshot)
                : null,
            delivery: is_array($row->delivery_snapshot)
                ? $this->mapDeliverySnapshot($row->delivery_snapshot)
                : null,
            payment: is_array($row->payment_snapshot)
                ? $this->mapPaymentSnapshot($row->payment_snapshot)
                : null,
            createdAt: new DateTimeImmutable((string) $row->created_at),
            confirmedAt: $row->confirmed_at !== null
                ? new DateTimeImmutable((string) $row->confirmed_at)
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Checkout $checkout): array
    {
        return [
            'id' => $checkout->id()->value(),
            'status' => $checkout->status()->value,
            'cart_snapshot' => $this->serializeCart($checkout->cart()),
            'client_snapshot' => $checkout->client() instanceof ClientSnapshot
                ? $this->serializeClient($checkout->client())
                : null,
            'delivery_snapshot' => $checkout->delivery() instanceof DeliverySnapshot
                ? $this->serializeDelivery($checkout->delivery())
                : null,
            'payment_snapshot' => $checkout->payment() instanceof PaymentSnapshot
                ? $this->serializePayment($checkout->payment())
                : null,
            'confirmed_at' => $checkout->confirmedAt(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapCartSnapshot(array $payload): CartSnapshot
    {
        $lines = [];

        foreach ($payload['lines'] ?? [] as $linePayload) {
            if (! is_array($linePayload)) {
                continue;
            }

            $lines[] = new CartLineSnapshot(
                productId: (int) ($linePayload['product_id'] ?? 0),
                productName: (string) ($linePayload['product_name'] ?? ''),
                quantity: (int) ($linePayload['quantity'] ?? 0),
                unitPrice: Money::rubles((int) ($linePayload['unit_price_rubles'] ?? 0)),
                payload: is_array($linePayload['payload'] ?? null) ? $linePayload['payload'] : null,
            );
        }

        return CartSnapshot::fromLines($lines);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapClientSnapshot(array $payload): ClientSnapshot
    {
        $kind = ClientKind::from((string) ($payload['kind'] ?? ClientKind::Guest->value));

        if ($kind === ClientKind::Registered) {
            return ClientSnapshot::registered(
                clientId: (int) ($payload['client_id'] ?? 0),
                name: isset($payload['name']) ? (string) $payload['name'] : null,
                phone: isset($payload['phone']) ? (string) $payload['phone'] : null,
                email: isset($payload['email']) ? (string) $payload['email'] : null,
            );
        }

        return ClientSnapshot::guest(
            new GuestContact(
                name: (string) ($payload['name'] ?? ''),
                phone: (string) ($payload['phone'] ?? ''),
                email: isset($payload['email']) ? (string) $payload['email'] : null,
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapDeliverySnapshot(array $payload): DeliverySnapshot
    {
        $addressPayload = $payload['address'] ?? null;

        return new DeliverySnapshot(
            method: DeliveryMethod::from((string) ($payload['method'] ?? DeliveryMethod::Courier->value)),
            address: is_array($addressPayload)
                ? new DeliveryAddress(
                    street: (string) ($addressPayload['street'] ?? ''),
                    house: (string) ($addressPayload['house'] ?? ''),
                    entrance: isset($addressPayload['entrance']) ? (string) $addressPayload['entrance'] : null,
                    apartment: isset($addressPayload['apartment']) ? (string) $addressPayload['apartment'] : null,
                )
                : null,
            comment: isset($payload['comment']) ? (string) $payload['comment'] : null,
            scheduledAt: isset($payload['scheduled_at']) ? (string) $payload['scheduled_at'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function mapPaymentSnapshot(array $payload): PaymentSnapshot
    {
        return new PaymentSnapshot(
            method: PaymentMethod::from((string) ($payload['method'] ?? PaymentMethod::Cash->value)),
            changeFromRubles: isset($payload['change_from_rubles'])
                ? (int) $payload['change_from_rubles']
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeCart(CartSnapshot $cart): array
    {
        return [
            'lines' => array_map(
                static fn (CartLineSnapshot $line): array => [
                    'product_id' => $line->productId(),
                    'product_name' => $line->productName(),
                    'quantity' => $line->quantity(),
                    'unit_price_rubles' => $line->unitPrice()->amountRubles(),
                    'line_total_rubles' => $line->lineTotal()->amountRubles(),
                    'payload' => $line->payload(),
                ],
                $cart->lines(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeClient(ClientSnapshot $client): array
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
    private function serializeDelivery(DeliverySnapshot $delivery): array
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
    private function serializePayment(PaymentSnapshot $payment): array
    {
        return [
            'method' => $payment->method()->value,
            'change_from_rubles' => $payment->changeFromRubles(),
        ];
    }
}
