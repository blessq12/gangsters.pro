<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\DTO\YandexMenuPromosRequestDto;

final class GetYandexFoodMenuPromosUseCase
{
    /**
     * Пустой promoItems — как в прежнем легаси-ответе promos.
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
