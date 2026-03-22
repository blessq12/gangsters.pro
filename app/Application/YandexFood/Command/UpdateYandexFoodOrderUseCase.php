<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\Acl\YandexFoodOrderPayloadHelper;
use App\Application\YandexFood\DTO\YandexUpdateOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Factories\CustomerSnapshotFactory;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Product\Repository\ProductRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use LogicException;
use Throwable;

final class UpdateYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    private const FAIL = 'Не удалось обновить заказ';

    public function __construct(
        OrderRepositoryInterface $orders,
        ProductRepository $products,
        CategoryRepository $categories,
        private readonly OrderFactory $orderFactory,
        private readonly OrderItemsFactory $itemsFactory,
        private readonly CustomerSnapshotFactory $customerFactory,
        private readonly ClientRepository $clients,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {
        parent::__construct($orders, $products, $categories);
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexUpdateOrderRequestDto $dto): array
    {
        try {
            try {
                $existing = $this->orders->getById($dto->id);
            } catch (ModelNotFoundException) {
                return [
                    'code' => 100,
                    'description' => 'Заказ не найден',
                ];
            }

            $p = $dto->payload;

            if (YandexFoodOrderPayloadHelper::isFullYandexUpdate($p)) {
                $err = YandexFoodOrderPayloadHelper::validateCreateShape($p, self::FAIL);
                if ($err !== null) {
                    return $err;
                }
                $order = $this->buildFullRebuiltOrder($dto->id, $existing, $p);
            } else {
                if (!isset($p['items']) || !is_array($p['items']) || $p['items'] === []) {
                    return YandexFoodOrderPayloadHelper::failure(self::FAIL);
                }
                foreach ($p['items'] as $item) {
                    if (!is_array($item) || !isset($item['id'], $item['quantity'], $item['price'])) {
                        return YandexFoodOrderPayloadHelper::failure(self::FAIL);
                    }
                }
                $order = $this->rebuildItemsOnlyOrder($existing, $p['items']);
            }

            $this->orders->save($order);

            return $this->yandexOrderContract->presentUpdateSuccess($order);
        } catch (LogicException $e) {
            Log::warning('UpdateYandexFoodOrderUseCase', ['message' => $e->getMessage()]);

            return YandexFoodOrderPayloadHelper::failure(self::FAIL);
        } catch (Throwable $e) {
            Log::error('UpdateYandexFoodOrderUseCase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return YandexFoodOrderPayloadHelper::failure(self::FAIL);
        }
    }

    /**
     * @param  array<string, mixed>  $p
     */
    private function buildFullRebuiltOrder(string $orderId, Order $existing, array $p): Order
    {
        $discriminator = (string) $p['discriminator'];
        $eatsId = $p['eatsId'];
        $restaurantId = $p['restaurantId'];

        $clientName = (string) $p['deliveryInfo']['clientName'];
        $phoneNumber = (string) $p['deliveryInfo']['phoneNumber'];
        $deliveryDate = Carbon::parse($p['deliveryInfo']['deliveryDate'])->format('Y-m-d H:i:s');
        $deliveryAddressFull = (string) $p['deliveryInfo']['deliveryAddress']['full'];
        $latitude = $p['deliveryInfo']['deliveryAddress']['latitude'];
        $longitude = $p['deliveryInfo']['deliveryAddress']['longitude'];

        $paymentType = (string) $p['paymentInfo']['paymentType'];

        $persons = $p['persons'];
        $comment = trim((string) ($p['comment'] ?? ''));
        $commentWithMeta = YandexFoodOrderPayloadHelper::appendYandexMetaToComment(
            $comment,
            $eatsId,
            $restaurantId,
            $p['paymentInfo'],
            $persons,
            $p['promos'] ?? [],
        );

        $deliveryAddress = [
            'full' => $deliveryAddressFull,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'delivery_at' => $deliveryDate,
        ];

        $clientId = YandexFoodOrderPayloadHelper::resolveClientId($p) ?? ($existing->getClientId() !== 0 ? $existing->getClientId() : null);
        $customerSnapshot = $this->buildCustomerSnapshot($clientId, $clientName, $phoneNumber);

        $deliveryInfo = new DeliveryInfo(
            method: $discriminator,
            address: $deliveryAddress,
            comment: $commentWithMeta !== '' ? $commentWithMeta : null,
        );

        $paymentInfo = new PaymentInfo(
            method: $paymentType,
            status: $existing->getPaymentInfo()?->status ?? PaymentStatus::Unpaid->value,
        );

        $customerSnapshotForOrder = new CustomerSnapshot(
            name: $customerSnapshot->name,
            phone: $customerSnapshot->phone,
            email: $customerSnapshot->email,
            address: $deliveryInfo->address,
        );

        $lineInputs = [];
        foreach ($p['items'] as $item) {
            $lineInputs[] = [
                'product_id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ];
        }

        $itemsData = $this->itemsFactory->buildItemsData($lineInputs);

        return $this->orderFactory->rebuildOrder(
            id: $orderId,
            clientId: $clientId ?? 0,
            customer: $customerSnapshotForOrder,
            status: $existing->getStatus(),
            itemsData: $itemsData,
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
            createdAt: $existing->getCreatedAt(),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsPayload
     */
    private function rebuildItemsOnlyOrder(Order $existing, array $itemsPayload): Order
    {
        $lineInputs = [];
        foreach ($itemsPayload as $item) {
            $lineInputs[] = [
                'product_id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ];
        }

        $itemsData = $this->itemsFactory->buildItemsData($lineInputs);

        $delivery = $existing->getDeliveryInfo();
        $customer = $existing->getCustomer();

        $customerSnapshotForOrder = new CustomerSnapshot(
            name: $customer->name,
            phone: $customer->phone,
            email: $customer->email,
            address: $delivery?->address,
        );

        return $this->orderFactory->rebuildOrder(
            id: $existing->getId(),
            clientId: $existing->getClientId(),
            customer: $customerSnapshotForOrder,
            status: $existing->getStatus(),
            itemsData: $itemsData,
            deliveryInfo: $delivery,
            paymentInfo: $existing->getPaymentInfo(),
            createdAt: $existing->getCreatedAt(),
        );
    }

    private function buildCustomerSnapshot(?int $clientId, string $yandexName, string $yandexPhone): CustomerSnapshot
    {
        if ($clientId !== null) {
            $client = $this->clients->findById($clientId);
            if ($client === null) {
                throw new LogicException('Client not found.');
            }
            if (!$client->isActive()) {
                throw new LogicException('Client is blocked or deleted.');
            }

            return $this->customerFactory->fromClient($client);
        }

        return new CustomerSnapshot(
            name: $yandexName,
            phone: $yandexPhone,
            email: null,
            address: null,
        );
    }
}
