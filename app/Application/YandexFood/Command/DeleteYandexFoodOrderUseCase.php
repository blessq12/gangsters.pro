<?php

namespace App\Application\YandexFood\Command;

use App\Application\Order\Contracts\OrderExternalLifecycleContract;
use App\Application\Order\Contracts\OrderReadContract;
use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\DTO\YandexDeleteOrderRequestDto;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DeleteYandexFoodOrderUseCase
{
    public function __construct(
        private readonly OrderReadContract $orders,
        private readonly OrderExternalLifecycleContract $orderLifecycle,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexDeleteOrderRequestDto $dto): array
    {
        try {
            $order = $this->orders->findPresentedById($dto->orderId);
            if ($order === null) {
                return [
                    'code' => 100,
                    'description' => 'Заказ не найден',
                ];
            }

            if ($dto->eatsId !== null && $dto->eatsId !== '') {
                $meta = $this->yandexOrderContract->integrationMetaByOrderId((string) $order['id']);
                $metaEats = $meta['yandex_eats_id'] ?? null;
                if ((string) $metaEats !== (string) $dto->eatsId) {
                    return [
                        'code' => 100,
                        'description' => 'Заказ не найден',
                    ];
                }
            }

            $cancelled = $this->orderLifecycle->cancelById((string) $order['id']);
            if (! $cancelled) {
                return [
                    'code' => 100,
                    'description' => 'Заказ не найден',
                ];
            }

            return $this->yandexOrderContract->presentDeleteSuccess($dto->orderId);
        } catch (Throwable $e) {
            Log::error('DeleteYandexFoodOrderUseCase', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'code' => 100,
                'description' => 'Заказ не найден',
            ];
        }
    }
}
