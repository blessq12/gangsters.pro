<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\DTO\YandexDeleteOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Repository\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DeleteYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        OrderRepositoryInterface $orders,
        ProductRepository $products,
        CategoryRepository $categories,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {
        parent::__construct($orders, $products, $categories);
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexDeleteOrderRequestDto $dto): array
    {
        try {
            try {
                $order = $this->orders->getById($dto->orderId);
            } catch (ModelNotFoundException) {
                return [
                    'code' => 100,
                    'description' => 'Заказ не найден',
                ];
            }

            if ($dto->eatsId !== null && $dto->eatsId !== '') {
                $meta = $this->yandexOrderContract->integrationMeta($order);
                $metaEats = $meta['yandex_eats_id'] ?? null;
                if ((string) $metaEats !== (string) $dto->eatsId) {
                    return [
                        'code' => 100,
                        'description' => 'Заказ не найден',
                    ];
                }
            }

            $this->orders->delete($dto->orderId);

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
