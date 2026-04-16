<?php

namespace App\Application\YandexFood\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\Contracts\CustomerSnapshotProvider;
use App\Application\Order\Contracts\UpdateOrderContract;
use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\Acl\YandexFoodOrderPayloadHelper;
use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Application\YandexFood\DTO\YandexUpdateOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UpdateYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    private const FAIL = 'Не удалось обновить заказ';

    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly UpdateOrderContract $updateOrder,
        private readonly CustomerSnapshotProvider $customerSnapshots,
        private readonly YandexFoodOrderMetaStore $metaStore,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {
        parent::__construct();
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
                $order = $this->buildFullRebuiltOrder($existing, $p);
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

            return $this->yandexOrderContract->presentUpdateSuccess($order);
        } catch (ApiException $e) {
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
    private function buildFullRebuiltOrder(Order $existing, array $p): Order
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
        $meta = YandexFoodOrderPayloadHelper::buildYandexMeta(
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

        $existingClientId = $existing->getClientId();
        $clientId = YandexFoodOrderPayloadHelper::resolveClientId($p)
            ?? ($existingClientId !== null && $existingClientId !== 0 ? $existingClientId : null);
        $customerSnapshot = $this->buildCustomerSnapshot($clientId, $clientName, $phoneNumber);

        $deliveryInfo = new DeliveryInfo(
            method: $discriminator,
            address: $deliveryAddress,
            comment: $comment !== '' ? $comment : null,
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

        $order = $this->updateOrder->update(
            existing: $existing,
            clientId: $clientId,
            customerSnapshot: $customerSnapshotForOrder,
            items: $lineInputs,
            deliveryInfo: $deliveryInfo,
            paymentInfo: $paymentInfo,
        );

        $this->metaStore->upsert($order->getId(), $meta);

        return $order;
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

        $delivery = $existing->getDeliveryInfo();
        $customer = $existing->getCustomer();

        return $this->updateOrder->update(
            existing: $existing,
            clientId: $existing->getClientId(),
            customerSnapshot: new CustomerSnapshot(
                name: $customer->name,
                phone: $customer->phone,
                email: $customer->email,
                address: $delivery?->address,
            ),
            items: $lineInputs,
            deliveryInfo: $delivery,
            paymentInfo: $existing->getPaymentInfo(),
        );
    }

    private function buildCustomerSnapshot(?int $clientId, string $yandexName, string $yandexPhone): CustomerSnapshot
    {
        if ($clientId !== null) {
            return $this->customerSnapshots->forAuthenticatedClient($clientId);
        }

        return $this->customerSnapshots->forExternalContact($yandexName, $yandexPhone);
    }
}
