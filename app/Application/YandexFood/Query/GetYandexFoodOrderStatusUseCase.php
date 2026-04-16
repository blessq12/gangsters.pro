<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\Acl\YandexFoodOrderContractPresenter;
use App\Application\YandexFood\DTO\YandexOrderIdRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;
use App\Application\Order\Contracts\OrderApplicationFacadeContract;

final class GetYandexFoodOrderStatusUseCase extends YandexFoodBaseUseCase
{
    public function __construct(
        private readonly OrderApplicationFacadeContract $orders,
        private readonly YandexFoodOrderContractPresenter $yandexOrderContract,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(YandexOrderIdRequestDto $dto): array
    {
        $order = $this->orders->findById($dto->id);
        if ($order === null) {
            return [
                'code' => 100,
                'description' => 'Заказ не найден',
            ];
        }

        return $this->yandexOrderContract->presentOrderStatus($order);
    }
}
