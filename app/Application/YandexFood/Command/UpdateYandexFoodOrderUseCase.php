<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\DTO\YandexUpdateOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class UpdateYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexUpdateOrderRequestDto $dto): array
    {
        return [];
    }
}
