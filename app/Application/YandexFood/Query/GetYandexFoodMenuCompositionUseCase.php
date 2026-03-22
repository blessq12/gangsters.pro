<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuCompositionRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class GetYandexFoodMenuCompositionUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexMenuCompositionRequestDto $dto): array
    {
        return [];
    }
}
