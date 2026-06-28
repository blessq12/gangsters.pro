<?php

namespace App\Domain\YandexFood\Exception;

use RuntimeException;

final class YandexFoodDisabledException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Интеграция Яндекс Еда отключена.');
    }
}
