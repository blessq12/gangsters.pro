<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexOrderIdRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class GetYandexFoodOrderStatusUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexOrderIdRequestDto $dto): array
    {
        return [];
    }
}
