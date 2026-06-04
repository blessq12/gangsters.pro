<?php

namespace App\Application\Order;

use App\Application\Order\Command\PlaceOrderWithChannelUseCase;
use App\Application\Order\Contracts\CancelOrderContract;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\Contracts\OrderExternalLifecycleContract;
use App\Application\Order\Contracts\OrderReadContract;
use App\Application\Order\Contracts\UpdateOrderContract;
use App\Application\Order\DTO\PlaceOrderInputDTO;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Order\Enums\OrderSource;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class OrderExternalOperations implements OrderExternalLifecycleContract, OrderReadContract
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly PlaceOrderWithChannelUseCase $placeOrder,
        private readonly UpdateOrderContract $updateOrder,
        private readonly CancelOrderContract $cancelOrder,
        private readonly CustomerSnapshotProvider $customerSnapshots,
        private readonly OrderPresenter $orderPresenter,
    ) {
    }

    public function placeExternalOrder(
        ?int $clientId,
        string $customerName,
        string $customerPhone,
        ?string $customerEmail,
        string $deliveryMethod,
        array $deliveryAddress,
        ?string $deliveryComment,
        string $paymentMethod,
        string $paymentStatus,
        array $items,
    ): array {
        $customer = $this->buildCustomerSnapshot(
            clientId: $clientId,
            name: $customerName,
            phone: $customerPhone,
            email: $customerEmail,
            address: $deliveryAddress,
        );

        $order = $this->placeOrder->execute(new PlaceOrderInputDTO(
            source: OrderSource::YandexFood,
            clientId: $clientId,
            customerSnapshot: $customer,
            items: $items,
            deliveryInfo: new DeliveryInfo(
                method: $deliveryMethod,
                address: $deliveryAddress,
                comment: $deliveryComment !== '' && $deliveryComment !== null ? $deliveryComment : null,
            ),
            paymentInfo: new PaymentInfo(
                method: $paymentMethod,
                status: $paymentStatus,
            ),
        ));

        return $this->orderPresenter->present($order);
    }

    public function findPresentedById(string $orderId): ?array
    {
        try {
            $order = $this->orders->getById($orderId);
        } catch (ModelNotFoundException) {
            return null;
        }

        return $this->orderPresenter->present($order);
    }

    public function updateExternalOrder(
        string $orderId,
        ?int $clientId,
        string $customerName,
        string $customerPhone,
        ?string $customerEmail,
        string $deliveryMethod,
        array $deliveryAddress,
        ?string $deliveryComment,
        string $paymentMethod,
        string $paymentStatus,
        array $items,
    ): ?array {
        try {
            $existing = $this->orders->getById($orderId);
        } catch (ModelNotFoundException) {
            return null;
        }

        $customer = $this->buildCustomerSnapshot(
            clientId: $clientId,
            name: $customerName,
            phone: $customerPhone,
            email: $customerEmail,
            address: $deliveryAddress,
        );

        $order = $this->updateOrder->update(
            existing: $existing,
            clientId: $clientId,
            customerSnapshot: $customer,
            items: $items,
            deliveryInfo: new DeliveryInfo(
                method: $deliveryMethod,
                address: $deliveryAddress,
                comment: $deliveryComment !== '' ? $deliveryComment : null,
            ),
            paymentInfo: new PaymentInfo(
                method: $paymentMethod,
                status: $paymentStatus,
            ),
        );

        return $this->orderPresenter->present($order);
    }

    public function updateOrderItems(string $orderId, array $items): ?array
    {
        try {
            $existing = $this->orders->getById($orderId);
        } catch (ModelNotFoundException) {
            return null;
        }

        $delivery = $existing->getDeliveryInfo();
        $customer = $existing->getCustomer();

        $order = $this->updateOrder->update(
            existing: $existing,
            clientId: $existing->getClientId(),
            customerSnapshot: new CustomerSnapshot(
                name: $customer->name,
                phone: $customer->phone,
                email: $customer->email,
                address: $delivery?->address,
            ),
            items: $items,
            deliveryInfo: $delivery,
            paymentInfo: $existing->getPaymentInfo(),
        );

        return $this->orderPresenter->present($order);
    }

    public function cancelById(string $orderId): bool
    {
        try {
            $existing = $this->orders->getById($orderId);
        } catch (ModelNotFoundException) {
            return false;
        }

        $this->cancelOrder->cancel($existing);

        return true;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function buildCustomerSnapshot(
        ?int $clientId,
        string $name,
        string $phone,
        ?string $email,
        array $address,
    ): CustomerSnapshot {
        if ($clientId !== null) {
            $snapshot = $this->customerSnapshots->forAuthenticatedClient($clientId);

            return new CustomerSnapshot(
                name: $snapshot->name,
                phone: $snapshot->phone,
                email: $snapshot->email,
                address: $address,
            );
        }

        $snapshot = $this->customerSnapshots->forExternalContact($name, $phone);

        return new CustomerSnapshot(
            name: $snapshot->name,
            phone: $snapshot->phone,
            email: $email,
            address: $address,
        );
    }
}
