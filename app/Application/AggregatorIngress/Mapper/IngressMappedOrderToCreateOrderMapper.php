<?php

namespace App\Application\AggregatorIngress\Mapper;

use App\Application\Order\DTO\CreateOrderFromIngressDto;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedAddress;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedLine;
use App\Domain\AggregatorIngress\ValueObject\IngressMappedOrder;
use App\Domain\AggregatorIngress\ValueObject\ResolvedPartnerProduct;
use App\Domain\Order\ValueObject\OrderCartSnapshot;
use App\Domain\Order\ValueObject\OrderClientSnapshot;
use App\Domain\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Order\ValueObject\OrderDeliverySnapshot;
use App\Domain\Order\ValueObject\OrderGuestContact;
use App\Domain\Order\ValueObject\OrderLineSnapshot;
use App\Domain\Order\ValueObject\OrderPaymentSnapshot;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\ValueObject\Money;

/**
 * ACL: IngressMappedOrder + резолв каталога → CreateOrderFromIngressDto.
 */
final class IngressMappedOrderToCreateOrderMapper
{
    /**
     * @param  list<array{line: IngressMappedLine, product: ResolvedPartnerProduct}>  $resolvedLines
     */
    public static function toCreateOrderDto(
        string $partnerCode,
        IngressMappedOrder $mapped,
        array $resolvedLines,
    ): CreateOrderFromIngressDto {
        $lines = [];

        foreach ($resolvedLines as $resolved) {
            $line = $resolved['line'];
            $product = $resolved['product'];

            $lines[] = new OrderLineSnapshot(
                productId: $product->productId,
                productName: $product->productName,
                quantity: $line->quantity,
                unitPrice: Money::rubles($line->unitPriceRubles),
                payload: [
                    'kind' => 'user',
                    'ingress_partner_sku' => $line->partnerSku,
                ],
                sku: $product->sku,
            );
        }

        $address = $mapped->deliveryAddress;

        return new CreateOrderFromIngressDto(
            partnerCode: $partnerCode,
            externalOrderId: $mapped->externalOrderId,
            cart: OrderCartSnapshot::fromLines($lines),
            client: OrderClientSnapshot::guest(
                new OrderGuestContact(
                    name: $mapped->clientName,
                    phone: $mapped->clientPhone,
                    email: $mapped->clientEmail,
                ),
            ),
            delivery: new OrderDeliverySnapshot(
                method: $mapped->deliveryMethod,
                address: $address instanceof IngressMappedAddress
                    ? new OrderDeliveryAddress(
                        street: $address->street,
                        house: $address->house,
                        entrance: $address->entrance,
                        apartment: $address->apartment,
                    )
                    : null,
                comment: $mapped->deliveryComment,
                scheduledAt: $mapped->deliveryScheduledAt,
            ),
            payment: new OrderPaymentSnapshot(
                method: $mapped->paymentMethod,
                changeFromRubles: $mapped->paymentChangeFromRubles,
            ),
            createdAt: $mapped->placedAt,
        );
    }
}
