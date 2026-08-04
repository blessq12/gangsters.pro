<?php

namespace App\Application\YandexFood\Port;

interface YandexFoodRestaurantInfoPort
{
    /**
     * @return array{title: string, address: string}
     */
    public function readRestaurantInfo(): array;
}
