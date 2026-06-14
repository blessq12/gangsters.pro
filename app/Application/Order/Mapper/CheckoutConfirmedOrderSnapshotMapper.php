<?php

namespace App\Application\Order\Mapper;

use App\Application\Order\DTO\CreateOrderDto;
use App\Domain\Checkout\Event\CheckoutConfirmed;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\DeliveryAddress;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use App\Domain\Checkout\ValueObject\GuestContact;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;
use App\Shared\Enum\ClientKind;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderGuestContact;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;

/**
 * ACL: слепки Checkout → слепки Order.
 */
final class CheckoutConfirmedOrderSnapshotMapper
{
    public static function toCreateOrderDto(CheckoutConfirmed $event): CreateOrderDto
    {
        return new CreateOrderDto(
            checkoutId: $event->checkoutId()->value(),
            cart: self::mapCart($event->cart()),
            client: self::mapClient($event->client()),
            delivery: self::mapDelivery($event->delivery()),
            payment: self::mapPayment($event->payment()),
            createdAt: $event->occurredAt(),
        );
    }

    private static function mapCart(\App\Domain\Checkout\ValueObject\CartSnapshot $cart): OrderCartSnapshot
    {
        $lines = array_map(
            static fn (CartLineSnapshot $line): OrderLineSnapshot => new OrderLineSnapshot(
                productId: $line->productId(),
                productName: $line->productName(),
                quantity: $line->quantity(),
                unitPrice: $line->unitPrice(),
                payload: $line->payload(),
            ),
            $cart->lines(),
        );

        return OrderCartSnapshot::fromLines($lines);
    }

    private static function mapClient(ClientSnapshot $client): OrderClientSnapshot
    {
        if ($client->kind() === ClientKind::Registered) {
            return OrderClientSnapshot::registered(
                clientId: (int) $client->clientId(),
                name: $client->name(),
                phone: $client->phone(),
                email: $client->email(),
            );
        }

        $guest = $client->guestContact();

        return OrderClientSnapshot::guest(
            new OrderGuestContact(
                name: $guest instanceof GuestContact ? $guest->name() : (string) $client->name(),
                phone: $guest instanceof GuestContact ? $guest->phone() : (string) $client->phone(),
                email: $guest instanceof GuestContact ? $guest->email() : $client->email(),
            ),
        );
    }

    private static function mapDelivery(DeliverySnapshot $delivery): OrderDeliverySnapshot
    {
        $address = $delivery->address();

        return new OrderDeliverySnapshot(
            method: $delivery->method(),
            address: $address instanceof DeliveryAddress
                ? new OrderDeliveryAddress(
                    street: $address->street(),
                    house: $address->house(),
                    entrance: $address->entrance(),
                    apartment: $address->apartment(),
                )
                : null,
            comment: $delivery->comment(),
            scheduledAt: $delivery->scheduledAt(),
        );
    }

    private static function mapPayment(PaymentSnapshot $payment): OrderPaymentSnapshot
    {
        return new OrderPaymentSnapshot(
            method: $payment->method(),
            changeFromRubles: $payment->changeFromRubles(),
        );
    }
}
