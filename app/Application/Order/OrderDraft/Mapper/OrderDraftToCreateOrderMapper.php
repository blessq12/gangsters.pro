<?php

namespace App\Application\Order\OrderDraft\Mapper;

use App\Application\Order\DTO\CreateOrderDto;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\ClientSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\DeliveryAddress;
use App\Domain\Order\OrderDraft\ValueObject\DeliverySnapshot;
use App\Domain\Order\OrderDraft\ValueObject\GuestContact;
use App\Domain\Order\OrderDraft\ValueObject\PaymentSnapshot;
use App\Shared\Enum\ClientKind;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderGuestContact;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use DateTimeImmutable;

/**
 * ACL: OrderDraft → CreateOrderDto.
 */
final class OrderDraftToCreateOrderMapper
{
    public static function toCreateOrderDto(
        OrderDraft $draft,
        string $clientRequestId,
        DateTimeImmutable $createdAt,
    ): CreateOrderDto {
        $client = $draft->client();
        $delivery = $draft->delivery();
        $payment = $draft->payment();

        if ($client === null || $delivery === null || $payment === null) {
            throw new \InvalidArgumentException('Черновик заказа неполный.');
        }

        return new CreateOrderDto(
            clientRequestId: $clientRequestId,
            cart: self::mapCart($draft->cart()->lines()),
            client: self::mapClient($client),
            delivery: self::mapDelivery($delivery),
            payment: self::mapPayment($payment),
            createdAt: $createdAt,
        );
    }

    /**
     * @param  list<CartLineSnapshot>  $lines
     */
    private static function mapCart(array $lines): OrderCartSnapshot
    {
        $mapped = array_map(
            static fn (CartLineSnapshot $line): OrderLineSnapshot => new OrderLineSnapshot(
                productId: $line->productId(),
                productName: $line->productName(),
                quantity: $line->quantity(),
                unitPrice: $line->unitPrice(),
                payload: $line->payload(),
                sku: $line->sku(),
            ),
            $lines,
        );

        return OrderCartSnapshot::fromLines($mapped);
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
