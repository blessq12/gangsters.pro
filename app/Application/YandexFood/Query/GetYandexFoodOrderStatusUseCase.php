<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\DTO\YandexOrderIdRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Product\Repository\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetYandexFoodOrderStatusUseCase extends YandexFoodBaseUseCase
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
    public function execute(YandexOrderIdRequestDto $dto): array
    {
        try {
            $order = $this->orders->getById($dto->id);

            return $this->yandexOrderContract->presentOrderStatus($order);
        } catch (ModelNotFoundException) {
            return [
                'code' => 100,
                'description' => 'Заказ не найден',
            ];
        }
    }
}
