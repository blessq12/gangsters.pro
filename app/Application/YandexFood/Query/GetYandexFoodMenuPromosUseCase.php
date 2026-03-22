<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuPromosRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class GetYandexFoodMenuPromosUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexMenuPromosRequestDto $dto): array
    {
        return [];
    }
}
