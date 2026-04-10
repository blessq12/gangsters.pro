<?php

namespace App\Application\Order\Presenter;

use App\Application\Order\DTO\OrderResponseDTO;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Support\Money;

final class OrderPresenter
{
    public function present(Order $order): array
    {
        return [
            'id' => $order->getId(),
            'client_id' => $order->getClientId(),
            'customer' => $this->presentCustomer($order->getCustomer()),
            'status' => $order->getStatus()->value,
            'subtotal' => Money::kopecksToApiRubles($order->getSubtotal()),
            'discount_total' => Money::kopecksToApiRubles($order->getDiscountTotal()),
            'total' => Money::kopecksToApiRubles($order->getTotal()),
            'delivery' => $order->getDeliveryInfo() !== null ? $this->presentDelivery($order->getDeliveryInfo()) : null,
            'payment' => $order->getPaymentInfo() !== null ? $this->presentPayment($order->getPaymentInfo()) : null,
            'items' => array_map(
                fn (OrderItem $item) => $this->presentItem($item),
                $order->getItems(),
            ),
            'created_at' => $order->getCreatedAt()->format(DATE_ATOM),
            'updated_at' => $order->getUpdatedAt()->format(DATE_ATOM),
        ];
    }

    public function toDTO(Order $order): OrderResponseDTO
    {
        $data = $this->present($order);

        return new OrderResponseDTO(
            id: $data['id'],
            clientId: $data['client_id'],
            customer: $data['customer'],
            status: $data['status'],
            subtotal: $data['subtotal'],
            discountTotal: $data['discount_total'],
            total: $data['total'],
            delivery: $data['delivery'],
            payment: $data['payment'],
            items: $data['items'],
            createdAt: $data['created_at'],
            updatedAt: $data['updated_at'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function presentCustomer(\App\Domain\Order\ValueObjects\CustomerSnapshot $customer): array
    {
        return [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentDelivery(DeliveryInfo $delivery): array
    {
        return [
            'method' => $delivery->method,
            'address' => $delivery->address,
            'comment' => $delivery->comment,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentPayment(PaymentInfo $payment): array
    {
        return [
            'method' => $payment->method,
            'status' => $payment->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentItem(OrderItem $item): array
    {
        $product = $item->getProduct();

        return [
            'id' => $item->getId(),
            'order_id' => $item->getOrderId(),
            'product_original_id' => $item->getProductOriginalId(),
            'product' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'list_price' => Money::kopecksToApiRubles($product->listPrice),
                'final_price' => Money::kopecksToApiRubles($product->finalPrice),
                'attributes' => $product->attributes,
                'media' => $product->media,
            ],
            'quantity' => $item->getQuantity(),
            'unit_price' => Money::kopecksToApiRubles($item->getUnitPrice()),
            'row_subtotal' => Money::kopecksToApiRubles($item->getRowSubtotal()),
            'row_discount' => Money::kopecksToApiRubles($item->getRowDiscount()),
            'row_total' => Money::kopecksToApiRubles($item->getRowTotal()),
        ];
    }
}
