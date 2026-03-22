<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entities\Order as OrderEntity;
use App\Domain\Order\Entities\OrderItem as OrderItemEntity;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\OrderStatus;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Order\ValueObjects\ProductSnapshot;
use App\Infrastructure\Order\Model\ORD_Order;
use App\Infrastructure\Order\Model\ORD_OrderItem;
use DateTimeImmutable;

class OrderRepository implements OrderRepositoryInterface
{
    public function getById(string $id): OrderEntity
    {
        /** @var ORD_Order $model */
        $model = ORD_Order::with('items')->findOrFail($id);

        return $this->mapToEntity($model);
    }

    /**
     * @return OrderEntity[]
     */
    public function findByClientId(int $clientId): array
    {
        $models = ORD_Order::with('items')
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get();

        return $models
            ->map(fn (ORD_Order $model) => $this->mapToEntity($model))
            ->all();
    }

    public function save(OrderEntity $order): void
    {
        /** @var ORD_Order $model */
        $model = ORD_Order::find($order->getId()) ?? new ORD_Order();

        $model->id = $order->getId();
        $model->client_id = $order->getClientId();
        $model->status = $order->getStatus()->value;
        $model->subtotal = $order->getSubtotal();
        $model->discount_total = $order->getDiscountTotal();
        $model->total = $order->getTotal();

        $customer = $order->getCustomer();
        $model->customer_name = $customer->name;
        $model->customer_phone = $customer->phone;
        $model->customer_email = $customer->email;
        $model->customer_address = $customer->address;

        $delivery = $order->getDeliveryInfo();
        $model->delivery_method = $delivery?->method;
        $model->delivery_address = $delivery?->address;
        $model->delivery_comment = $delivery?->comment;

        $payment = $order->getPaymentInfo();
        $model->payment_method = $payment?->method;
        $model->payment_status = $payment?->status;

        $model->save();

        // sync items (simple strategy: delete & recreate)
        ORD_OrderItem::where('order_id', $model->id)->delete();

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();

            $itemModel = new ORD_OrderItem();
            $itemModel->order_id = $model->id;
            $itemModel->product_original_id = $item->getProductOriginalId();
            $itemModel->product_name = $product->name;
            $itemModel->product_sku = $product->sku;
            $itemModel->product_list_price = $product->listPrice;
            $itemModel->product_final_price = $product->finalPrice;
            $itemModel->product_attributes = $product->attributes;
            $itemModel->product_media = $product->media;
            $itemModel->quantity = $item->getQuantity();
            $itemModel->unit_price = $item->getUnitPrice();
            $itemModel->row_subtotal = $item->getRowSubtotal();
            $itemModel->row_discount = $item->getRowDiscount();
            $itemModel->row_total = $item->getRowTotal();

            $itemModel->save();
        }
    }

    public function delete(string $id): void
    {
        ORD_OrderItem::where('order_id', $id)->delete();
        ORD_Order::where('id', $id)->delete();
    }

    private function mapToEntity(ORD_Order $model): OrderEntity
    {
        $customer = new CustomerSnapshot(
            name: $model->customer_name,
            phone: $model->customer_phone,
            email: $model->customer_email,
            address: $model->customer_address,
        );

        $delivery = $model->delivery_method !== null
            ? new DeliveryInfo(
                method: $model->delivery_method,
                address: $model->delivery_address,
                comment: $model->delivery_comment,
            )
            : null;

        $payment = $model->payment_method !== null
            ? new PaymentInfo(
                method: $model->payment_method,
                status: $model->payment_status,
            )
            : null;

        $items = [];
        foreach ($model->items as $itemModel) {
            $productSnapshot = new ProductSnapshot(
                name: $itemModel->product_name,
                sku: $itemModel->product_sku,
                listPrice: (int) $itemModel->product_list_price,
                finalPrice: (int) $itemModel->product_final_price,
                attributes: $itemModel->product_attributes ?? [],
                media: $itemModel->product_media ?? [],
            );

            $items[] = new OrderItemEntity(
                id: (string) $itemModel->id,
                orderId: $model->id,
                productOriginalId: $itemModel->product_original_id,
                product: $productSnapshot,
                quantity: (int) $itemModel->quantity,
                unitPrice: (int) $itemModel->unit_price,
                rowSubtotal: (int) $itemModel->row_subtotal,
                rowDiscount: (int) $itemModel->row_discount,
                rowTotal: (int) $itemModel->row_total,
            );
        }

        $createdAt = $model->created_at instanceof \DateTimeInterface
            ? DateTimeImmutable::createFromInterface($model->created_at)
            : new DateTimeImmutable();

        $updatedAt = $model->updated_at instanceof \DateTimeInterface
            ? DateTimeImmutable::createFromInterface($model->updated_at)
            : $createdAt;

        return new OrderEntity(
            id: $model->id,
            clientId: (int) $model->client_id,
            customer: $customer,
            status: OrderStatus::from($model->status),
            subtotal: (int) $model->subtotal,
            discountTotal: (int) $model->discount_total,
            total: (int) $model->total,
            deliveryInfo: $delivery,
            paymentInfo: $payment,
            items: $items,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }
}

