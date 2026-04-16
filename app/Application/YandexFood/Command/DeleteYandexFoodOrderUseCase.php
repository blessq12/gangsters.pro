<?php

namespace App\Application\YandexFood\Command;

use App\Application\Order\Contracts\OrderApplicationFacadeContract;
use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\DTO\YandexDeleteOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DeleteYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        private readonly OrderApplicationFacadeContract $orders,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexDeleteOrderRequestDto $dto): array
    {
        try {
            $order = $this->orders->findById($dto->orderId);
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

            $cancelled = $this->orders->cancelById((string) $order['id']);
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
