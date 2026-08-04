<?php

namespace App\Infrastructure\YandexFood\Exception;

use Exception;

final class YandexFoodOAuthRejectedException extends Exception
{
    public function __construct(string $message = 'OAuth-запрос Яндекс.Еда отклонён.')
    {
        parent::__construct($message);
    }
}
