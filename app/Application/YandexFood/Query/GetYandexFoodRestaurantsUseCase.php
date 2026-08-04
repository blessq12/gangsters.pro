<?php

namespace App\Application\YandexFood\Query;

use App\Application\YandexFood\Port\YandexFoodRestaurantInfoPort;

final class GetYandexFoodRestaurantsUseCase
{
    public function __construct(
        private readonly YandexFoodRestaurantInfoPort $restaurantInfo,
    ) {}

    /**
     * @return array{places: list<array{id: string, title: string, address: string}>}
     */
    public function execute(): array
    {
        $info = $this->restaurantInfo->readRestaurantInfo();
        $restaurantId = config('yandex_food.restaurant_id', '1');

        return [
            'places' => [
                [
                    'id' => is_string($restaurantId) && $restaurantId !== '' ? $restaurantId : '1',
                    'title' => $info['title'],
                    'address' => $info['address'],
                ],
            ],
        ];
    }
}
