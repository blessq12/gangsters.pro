<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderDTO;
use App\Application\Order\OrderBaseUseCase;
use App\Application\Order\Presenter\OrderPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\VO\CustomerStatus as ProductCustomerStatus;
use LogicException;

final class CreateOrderUseCase extends OrderBaseUseCase
{
    public function __construct(
        OrderRepositoryInterface $orders,
        \App\Domain\Order\Factories\OrderFactory $orderFactory,
        \App\Domain\Client\Repository\ClientRepository $clients,
        \App\Domain\Product\Repository\ProductRepository $products,
        private readonly OrderPresenter $presenter,
    ) {
        parent::__construct($orders, $orderFactory, $clients, $products);
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateOrderDTO $dto): array
    {
        if (\count($dto->items) === 0) {
            throw new LogicException('Order must contain at least one item.');
        }

        $client = null;
        $customerSnapshot = null;

        if ($dto->clientId !== null) {
            $client = $this->clients->findById($dto->clientId);
            if ($client === null) {
                throw new LogicException('Client not found.');
            }
            if (!$client->isActive()) {
                throw new LogicException('Client is blocked or deleted.');
            }

            $customerSnapshot = $this->buildCustomerSnapshot($client);
        } else {
            $customerSnapshot = new CustomerSnapshot(
                name: 'Гость',
                phone: '',
                email: null,
                address: null,
            );
        }
        $itemsData = $this->buildItemsData($dto->items);

        $deliveryInfo = new DeliveryInfo(
            method: $dto->deliveryMethod,
            address: $dto->deliveryAddress,
            comment: $dto->deliveryComment,
        );

        $paymentInfo = new PaymentInfo(
            method: $dto->paymentMethod,
            status: PaymentStatus::Unpaid->value,
        );

        // Генерируем короткий номер заказа вида ORD-XXXXXX
        $orderId = 'ORD-' . random_int(100000, 999999);
        $order = $this->orderFactory->create(
            $orderId,
            $dto->clientId ?? 0,
            $customerSnapshot,
            $itemsData,
            $deliveryInfo,
            $paymentInfo,
        );

        $this->orders->save($order);

        return $this->presenter->present($order);
    }

    private function buildCustomerSnapshot(Client $client): CustomerSnapshot
    {
        $address = null;
        $addresses = $client->addresses();
        if (\count($addresses) > 0) {
            $addr = $client->defaultAddressId() !== null
                ? $this->findAddressById($addresses, $client->defaultAddressId())
                : $addresses[0];
            if ($addr !== null) {
                $address = [
                    'street' => $addr->street(),
                    'house' => $addr->house(),
                    'entrance' => $addr->entrance(),
                    'apartment' => $addr->apartment(),
                ];
            }
        }

        return new CustomerSnapshot(
            name: $client->name(),
            phone: (string) $client->phone(),
            email: $client->email() !== null ? (string) $client->email() : null,
            address: $address,
        );
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array<int, array{productOriginalId: int|null, name: string, sku: string, listPrice: int, finalPrice: int, quantity: int, attributes: array, media: array}>
     */
    private function buildItemsData(array $items): array
    {
        $productIds = array_unique(array_column($items, 'product_id'));
        $products = $this->products->findByIds($productIds);
        $productsById = [];
        foreach ($products as $p) {
            $id = $p->id();
            if ($id !== null) {
                $productsById[$id] = $p;
            }
        }

        $customerStatus = new ProductCustomerStatus('regular');
        $result = [];

        foreach ($items as $row) {
            $productId = $row['product_id'];
            $quantity = $row['quantity'];

            $product = $productsById[$productId] ?? null;
            if ($product === null) {
                throw new LogicException("Product not found: {$productId}");
            }
            if ($product->status() !== Product::STATUS_ACTIVE) {
                throw new LogicException("Product is not available: {$productId}");
            }

            $priceVO = $product->priceForStatus($customerStatus);
            if ($priceVO === null && \count($product->prices()) > 0) {
                $priceVO = $product->prices()[0];
            }
            if ($priceVO === null) {
                throw new LogicException("Product has no price: {$productId}");
            }

            $amount = $priceVO->amount();
            $result[] = [
                'productOriginalId' => $product->id(),
                'name' => $product->name(),
                'sku' => $product->articul() ?? (string) $product->id(),
                'listPrice' => $amount,
                'finalPrice' => $amount,
                'quantity' => $quantity,
                'attributes' => [],
                'media' => $this->productImagesToMedia($product->images()),
            ];
        }

        return $result;
    }

    /**
     * @param \App\Domain\Product\Entity\ProductImage[] $images
     * @return array<int, array<string, mixed>>
     */
    private function productImagesToMedia(array $images): array
    {
        $out = [];
        foreach ($images as $img) {
            foreach ($img->variants() as $v) {
                $out[] = [
                    'url' => $v->path(),
                    'variant' => $v->size(),
                ];
            }
        }

        return $out;
    }

    /**
     * @param \App\Domain\Client\Entity\ClientAddress[] $addresses
     */
    private function findAddressById(array $addresses, int $id): ?\App\Domain\Client\Entity\ClientAddress
    {
        foreach ($addresses as $a) {
            if ($a->id() === $id) {
                return $a;
            }
        }

        return null;
    }
}
