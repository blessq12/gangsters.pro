<?php

namespace App\Application\YandexFood\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Order\Contracts\OrderApplicationFacadeContract;
use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\Acl\YandexFoodOrderPayloadHelper;
use App\Application\YandexFood\Contracts\YandexFoodOrderMetaStore;
use App\Application\YandexFood\DTO\YandexCreateOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Вертикаль (как у {@see \App\Application\Order\Command\CreateOrderUseCase}):
 * HTTP payload Еды → валидация формы → слепок клиента (клиент из БД или гость) →
 * {@see OrderItemsFactory::buildItemsData()} (цены/снимки из доменного каталога) →
 * {@see DeliveryInfo} + {@see PaymentInfo} → выравнивание адреса в {@see CustomerSnapshot} →
 * {@see OrderFactory::create()} → {@see OrderRepositoryInterface::save()} →
 * {@see OrderCreated} → {@see YandexFoodOrderContractPresenter::presentCreateSuccess()}.
 *
 * Отличия входа: нет {@see \App\Application\Order\DTO\CreateOrderDTO}, данные из JSON Яндекса;
 * при отсутствии client_id имя/телефон берутся из deliveryInfo (у гостя в SPA они остаются из forGuest()).
 */
final class CreateYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        private readonly OrderApplicationFacadeContract $orders,
        private readonly YandexFoodOrderMetaStore $metaStore,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexCreateOrderRequestDto $dto): array
    {
        $data = $dto->payload;
        Log::info('CreateYandexFoodOrderUseCase', ['data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);

        try {
            $error = YandexFoodOrderPayloadHelper::validateCreateShape(
                $data,
                'Не удалось создать заказ',
            );
            if ($error !== null) {
                return $error;
            }

            $discriminator = (string) $data['discriminator'];
            $eatsId = $data['eatsId'];
            $restaurantId = $data['restaurantId'];

            $clientName = (string) $data['deliveryInfo']['clientName'];
            $phoneNumber = (string) $data['deliveryInfo']['phoneNumber'];
            $deliveryDate = Carbon::parse($data['deliveryInfo']['deliveryDate'])->format('Y-m-d H:i:s');
            $deliveryAddressFull = (string) $data['deliveryInfo']['deliveryAddress']['full'];
            $latitude = $data['deliveryInfo']['deliveryAddress']['latitude'];
            $longitude = $data['deliveryInfo']['deliveryAddress']['longitude'];

            $paymentType = (string) $data['paymentInfo']['paymentType'];

            $persons = $data['persons'];
            $comment = trim((string) ($data['comment'] ?? ''));
            $meta = YandexFoodOrderPayloadHelper::buildYandexMeta(
                $eatsId,
                $restaurantId,
                $data['paymentInfo'],
                $persons,
                $data['promos'] ?? [],
            );

            $deliveryAddress = [
                'full' => $deliveryAddressFull,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'delivery_at' => $deliveryDate,
            ];

            $clientId = YandexFoodOrderPayloadHelper::resolveClientId($data);

            $lineInputs = array_map(
                static fn (array $item): array => [
                    'product_id' => (int) $item['id'],
                    'quantity' => (int) $item['quantity'],
                ],
                $data['items'],
            );

            $order = $this->orders->placeExternalOrder(
                clientId: $clientId,
                customerName: $clientName,
                customerPhone: $phoneNumber,
                customerEmail: null,
                deliveryMethod: $discriminator,
                deliveryAddress: $deliveryAddress,
                deliveryComment: $comment !== '' ? $comment : null,
                paymentMethod: $paymentType,
                paymentStatus: 'unpaid',
                items: $lineInputs,
            );
            $this->metaStore->upsert((string) $order['id'], $meta);

            return $this->yandexOrderContract->presentCreateSuccess($order);
        } catch (ApiException $e) {
            Log::warning('CreateYandexFoodOrderUseCase', ['message' => $e->getMessage()]);

            return YandexFoodOrderPayloadHelper::failure('Не удалось создать заказ');
        } catch (Throwable $e) {
            Log::error('CreateYandexFoodOrderUseCase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return YandexFoodOrderPayloadHelper::failure('Не удалось создать заказ');
        }
    }

}
