<?php

namespace App\Application\YandexFood\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\Contracts\OrderExternalLifecycleContract;
use App\Application\Order\Contracts\OrderReadContract;
use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\Acl\YandexFoodOrderPayloadMapper;
use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Application\YandexFood\DTO\YandexUpdateOrderRequestDto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UpdateYandexFoodOrderUseCase
{
    private const FAIL = 'Не удалось обновить заказ';

    public function __construct(
        private readonly OrderReadContract $orders,
        private readonly OrderExternalLifecycleContract $orderLifecycle,
        private readonly YandexFoodOrderMetaStore $metaStore,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexUpdateOrderRequestDto $dto): array
    {
        try {
            $existing = $this->orders->findPresentedById($dto->id);
            if ($existing === null) {
                return [
                    'code' => 100,
                    'description' => 'Заказ не найден',
                ];
            }

            $p = $dto->payload;

            if (YandexFoodOrderPayloadMapper::isFullYandexUpdate($p)) {
                $err = YandexFoodOrderPayloadMapper::validateCreateShape($p, self::FAIL);
                if ($err !== null) {
                    return $err;
                }
                $order = $this->buildFullRebuiltOrder($existing, $p);
            } else {
                if (! isset($p['items']) || ! is_array($p['items']) || $p['items'] === []) {
                    return YandexFoodOrderPayloadMapper::failure(self::FAIL);
                }
                foreach ($p['items'] as $item) {
                    if (! is_array($item) || ! isset($item['id'], $item['quantity'], $item['price'])) {
                        return YandexFoodOrderPayloadMapper::failure(self::FAIL);
                    }
                }
                $order = $this->rebuildItemsOnlyOrder((string) $existing['id'], $p['items']);
            }

            return $this->yandexOrderContract->presentUpdateSuccess($order);
        } catch (ApiException $e) {
            Log::warning('UpdateYandexFoodOrderUseCase', ['message' => $e->getMessage()]);

            return YandexFoodOrderPayloadMapper::failure(self::FAIL);
        } catch (Throwable $e) {
            Log::error('UpdateYandexFoodOrderUseCase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return YandexFoodOrderPayloadMapper::failure(self::FAIL);
        }
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $p
     * @return array<string, mixed>
     */
    private function buildFullRebuiltOrder(array $existing, array $p): array
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
        $meta = YandexFoodOrderPayloadMapper::buildYandexMeta(
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

        $existingClientId = isset($existing['client_id']) ? (int) $existing['client_id'] : null;
        $clientId = YandexFoodOrderPayloadMapper::resolveClientId($p)
            ?? ($existingClientId !== null && $existingClientId !== 0 ? $existingClientId : null);
        $existingPayment = is_array($existing['payment'] ?? null) ? $existing['payment'] : [];

        $lineInputs = [];
        foreach ($p['items'] as $item) {
            $lineInputs[] = [
                'product_id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ];
        }

        $order = $this->orderLifecycle->updateExternalOrder(
            orderId: (string) $existing['id'],
            clientId: $clientId,
            customerName: $clientName,
            customerPhone: $phoneNumber,
            customerEmail: null,
            deliveryMethod: $discriminator,
            deliveryAddress: $deliveryAddress,
            deliveryComment: $comment !== '' ? $comment : null,
            paymentMethod: $paymentType,
            paymentStatus: (string) ($existingPayment['status'] ?? 'unpaid'),
            items: $lineInputs,
        );
        if ($order === null) {
            throw new ApiException(self::FAIL);
        }

        $this->metaStore->upsert((string) $order['id'], $meta);

        return $order;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemsPayload
     * @return array<string, mixed>
     */
    private function rebuildItemsOnlyOrder(string $orderId, array $itemsPayload): array
    {
        $lineInputs = [];
        foreach ($itemsPayload as $item) {
            $lineInputs[] = [
                'product_id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ];
        }

        $order = $this->orderLifecycle->updateOrderItems(
            orderId: $orderId,
            items: $lineInputs,
        );
        if ($order === null) {
            throw new ApiException(self::FAIL);
        }

        return $order;
    }
}
