<?php

namespace App\Application\YandexFood\Command;

use App\Application\YandexFood\DTO\YandexCreateOrderRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class CreateYandexFoodOrderUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexCreateOrderRequestDto $dto): array
    {
        return [];
    }
}
