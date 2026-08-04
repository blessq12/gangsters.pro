<?php

namespace App\Infrastructure\YandexFood\Exception;

use Exception;

final class YandexFoodDisabledException extends Exception
{
    public function __construct(string $message = 'Интеграция Яндекс.Еда отключена.')
    {
        parent::__construct($message);
    }
}
