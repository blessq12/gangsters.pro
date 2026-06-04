<?php

namespace App\Application\Operations\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Order\DTO\CreateAdminOrderDTO;
use App\Application\Operations\Order\Query\GetAdminOrderDetailQuery;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\Command\PlaceOrderWithChannelUseCase;
use App\Application\Order\DTO\PlaceOrderInputDTO;
use App\Domain\Order\Enums\OrderSource;
use App\Application\Shopping\Delivery\ResolveDeliveryPricing;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;

final class CreateAdminOrderUseCase
{
    public function __construct(
        private readonly CustomerSnapshotProvider $customerSnapshots,
        private readonly PlaceOrderWithChannelUseCase $placeOrder,
        private readonly ResolveDeliveryPricing $resolveDeliveryPricing,
        private readonly GetAdminOrderDetailQuery $orderDetail,
    ) {
    }

    public function execute(CreateAdminOrderDTO $dto): array
    {
        if ($dto->items === []) {
            throw new ApiException('Order must contain at least one item.', 422);
        }

        if ($dto->clientId !== null) {
            $customerSnapshot = $this->customerSnapshots->forAuthenticatedClient($dto->clientId);
            $clientId = $dto->clientId;
        } else {
            $name = trim((string) ($dto->guestCustomerName ?? ''));
            $phone = trim((string) ($dto->guestCustomerPhone ?? ''));
            if ($name === '' || $phone === '') {
                throw new ApiException('Guest order requires customer name and phone.', 422);
            }

            $customerSnapshot = $this->customerSnapshots->forGuestContact(
                $name,
                $phone,
                $dto->guestCustomerEmail,
            );
            $clientId = null;
        }

        $deliveryInfo = new DeliveryInfo(
            method: $dto->deliveryMethod,
            address: $dto->deliveryAddress,
            comment: $dto->deliveryComment,
        );

        $paymentInfo = new PaymentInfo(
            method: $dto->paymentMethod,
            status: PaymentStatus::Unpaid->value,
        );

        $customerSnapshotForOrder = new CustomerSnapshot(
            name: $customerSnapshot->name,
            phone: $customerSnapshot->phone,
            email: $customerSnapshot->email,
            address: $deliveryInfo->address,
        );

        $deliveryPricing = $this->resolveDeliveryPricing->fromPlacementRows($dto->items, $dto->deliveryMethod);

        $order = $this->placeOrder->execute(new PlaceOrderInputDTO(
            source: OrderSource::Admin,
            clientId: $clientId,
            customerSnapshot: $customerSnapshotForOrder,
            items: $dto->items,
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            deliveryFeeKopecks: $deliveryPricing->deliveryFeeKopecks,
            deliveryPricingSnapshot: $deliveryPricing->toSnapshotArray(),
        ));

        return $this->orderDetail->execute($order->getId());
    }
}
