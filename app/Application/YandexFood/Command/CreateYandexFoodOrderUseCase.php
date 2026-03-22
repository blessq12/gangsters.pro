<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\Acl\YandexFoodOrderPayloadHelper;
use App\Application\YandexFood\DTO\YandexCreateOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Order\Enums\PaymentStatus;
use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Factories\CustomerSnapshotFactory;
use App\Domain\Order\Factories\OrderFactory;
use App\Domain\Order\Factories\OrderItemsFactory;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\CustomerSnapshot;
use App\Domain\Order\ValueObjects\DeliveryInfo;
use App\Domain\Order\ValueObjects\PaymentInfo;
use App\Domain\Product\Repository\ProductRepository;
use App\Shared\Events\DomainEventBus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use LogicException;
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
        OrderRepositoryInterface $orders,
        ProductRepository $products,
        CategoryRepository $categories,
        private readonly OrderFactory $orderFactory,
        private readonly OrderItemsFactory $itemsFactory,
        private readonly CustomerSnapshotFactory $customerFactory,
        private readonly ClientRepository $clients,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
        private readonly DomainEventBus $events,
    ) {
        parent::__construct($orders, $products, $categories);
    }

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
            $commentWithMeta = YandexFoodOrderPayloadHelper::appendYandexMetaToComment(
                $comment,
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

            $customerSnapshot = $this->buildCustomerSnapshot($clientId, $clientName, $phoneNumber);

            $deliveryInfo = new DeliveryInfo(
                method: $discriminator,
                address: $deliveryAddress,
                comment: $commentWithMeta !== '' ? $commentWithMeta : null,
            );

            $paymentInfo = new PaymentInfo(
                method: $paymentType,
                status: PaymentStatus::Unpaid->value,
            );

            // Адрес в слепке клиента должен совпадать с адресом доставки заказа (как в CreateOrderUseCase).
            $customerSnapshotForOrder = new CustomerSnapshot(
                name: $customerSnapshot->name,
                phone: $customerSnapshot->phone,
                email: $customerSnapshot->email,
                address: $deliveryInfo->address,
            );

            $lineInputs = [];
            foreach ($data['items'] as $item) {
                $lineInputs[] = [
                    'product_id' => (int) $item['id'],
                    'quantity' => (int) $item['quantity'],
                ];
            }

            $itemsData = $this->itemsFactory->buildItemsData($lineInputs);

            $order = $this->orderFactory->create(
                clientId: $clientId ?? 0,
                customer: $customerSnapshotForOrder,
                itemsData: $itemsData,
                deliveryInfo: $deliveryInfo,
                paymentInfo: $paymentInfo,
            );

            $this->orders->save($order);
            $this->events->publish(new OrderCreated($order));

            return $this->yandexOrderContract->presentCreateSuccess($order);
        } catch (LogicException $e) {
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

        // Нет client_id: как forGuest(), но ФИО/телефон приходят из Еды.
        return new CustomerSnapshot(
            name: $yandexName,
            phone: $yandexPhone,
            email: null,
            address: null,
        );
    }

}
