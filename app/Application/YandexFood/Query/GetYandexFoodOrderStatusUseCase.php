<?php

namespace App\Application\YandexFood\Query;

use App\Application\Order\Contracts\OrderReadContract;
use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\DTO\YandexOrderIdRequestDto;

final class GetYandexFoodOrderStatusUseCase
{
    public function __construct(
        private readonly OrderReadContract $orders,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexOrderIdRequestDto $dto): array
    {
        $order = $this->orders->findPresentedById($dto->id);
        if ($order === null) {
            return [
                'code' => 100,
                'description' => 'Заказ не найден',
            ];
        }

        return $this->yandexOrderContract->presentOrderStatus($order);
    }
}
