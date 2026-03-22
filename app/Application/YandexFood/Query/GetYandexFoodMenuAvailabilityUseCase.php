<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuAvailabilityRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class GetYandexFoodMenuAvailabilityUseCase extends YandexFoodBaseUseCase
{
    /**
     * @return array<string, mixed>
     */
    public function execute(YandexMenuAvailabilityRequestDto $dto): array
    {
        return [];
    }
}
