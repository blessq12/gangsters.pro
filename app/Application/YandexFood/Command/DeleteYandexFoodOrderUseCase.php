<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\DTO\YandexDeleteOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class DeleteYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexDeleteOrderRequestDto $dto): array
    {
        return [];
    }
}
