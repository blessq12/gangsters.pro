<?php

namespace App\Domain\Order\Enums;

enum OrderSource: string
{
    case Site = 'site';
    case Admin = 'admin';
    case YandexFood = 'yandex_food';
}
