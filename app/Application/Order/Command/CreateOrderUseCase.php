<?php

namespace App\Application\Order\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\DTO\PlaceOrderInputDTO;
use App\Domain\Order\Enums\OrderSource;
use App\Application\Order\OrderBaseUseCase;
use App\Application\Order\Presenter\OrderPresenter;
use App\Application\Shopping\CartRules\ResolvedCartOrderItemsMapper;
use App\Application\Shopping\CartRules\ResolveShoppingCartUseCase;
use App\Application\Shopping\Delivery\ResolveDeliveryPricing;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use App\Shared\Auth\ClientAuthContext;
use App\Shared\Events\DomainEventBus;

final class CreateOrderUseCase extends OrderBaseUseCase
{
    public function __construct(
        OrderRepositoryInterface $orders,
        OrderFactory $orderFactory,
        CustomerSnapshotProvider $customerSnapshots,
        ClientAuthContext $authContext,
        OrderItemsFactory $itemsFactory,
        OrderPresenter $presenter,
        DomainEventBus $events,
        private readonly PlaceOrderWithChannelUseCase $placeOrder,
        private readonly ShoppingSessionRepositoryInterface $shoppingSessions,
        private readonly ResolveShoppingCartUseCase $resolveShoppingCart,
        private readonly ResolveDeliveryPricing $resolveDeliveryPricing,
    ) {
        parent::__construct(
            $orders,
            $orderFactory,
            $customerSnapshots,
            $authContext,
            $itemsFactory,
            $presenter,
            $events,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateOrderDTO $dto, ?int $authenticatedClientId, ?ShoppingSession $shoppingSession = null): array
    {
        $items = $dto->items;
        $resolvedCart = null;
        if ($shoppingSession !== null && ! $shoppingSession->isEmptyCart()) {
            $resolvedCart = $this->resolveShoppingCart->execute($shoppingSession);
            $items = ResolvedCartOrderItemsMapper::toOrderPlacementRows($resolvedCart);
        }

        if ($authenticatedClientId !== null && $shoppingSession !== null) {
            $sid = $shoppingSession->getClientId();
            if ($sid !== null && $sid !== $authenticatedClientId) {
                throw new ApiException('Shopping session does not belong to the current client.', 403);
            }
        }

        if (\count($items) === 0) {
            throw new ApiException('Order must contain at least one item.');
        }

        if ($authenticatedClientId !== null) {
            $customerSnapshot = $this->customerSnapshots->forAuthenticatedClient($authenticatedClientId);
            $clientId = $authenticatedClientId;
        } else {
            $name = $dto->guestCustomerName ?? '';
            $phone = $dto->guestCustomerPhone ?? '';
            if (trim($name) === '' || trim($phone) === '') {
                throw new ApiException('Guest order requires customer name and phone.');
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

        $deliveryPricing = $resolvedCart instanceof CartState
            ? $this->resolveDeliveryPricing->fromCartState($resolvedCart, $dto->deliveryMethod)
            : $this->resolveDeliveryPricing->fromPlacementRows($items, $dto->deliveryMethod);

        $order = $this->placeOrder->execute(new PlaceOrderInputDTO(
            source: OrderSource::Site,
            clientId: $clientId,
            customerSnapshot: $customerSnapshotForOrder,
            items: $items,
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            deliveryFeeKopecks: $deliveryPricing->deliveryFeeKopecks,
            deliveryPricingSnapshot: $deliveryPricing->toSnapshotArray(),
        ));

        if ($shoppingSession !== null) {
            $shoppingSession->clearCart();
            $shoppingSession->setCheckoutDraft(null);
            $this->shoppingSessions->save($shoppingSession);
        }

        return $this->presenter->present($order);
    }
}
