<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuPromosRequestDto;
use App\Application\YandexFood\YandexFoodBaseUseCase;

final class GetYandexFoodMenuPromosUseCase extends YandexFoodBaseUseCase
{
    /**
     * Как {@see \App\Services\Yandex\YandexFoodMenuService::getMenuPromos()}.
     *
     * @return array{promoItems: array<int, mixed>}
     */
    public function execute(YandexMenuPromosRequestDto $dto): array
    {
        return [
            'promoItems' => [],
        ];
    }
}
