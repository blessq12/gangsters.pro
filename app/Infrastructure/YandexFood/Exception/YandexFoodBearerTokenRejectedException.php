<?php

namespace App\Infrastructure\YandexFood\Exception;

use Exception;

final class YandexFoodBearerTokenRejectedException extends Exception
{
    public function __construct(string $message = 'Bearer-токен Яндекс.Еда отклонён.')
    {
        parent::__construct($message);
    }

    public function reason(): string
    {
        return $this->getMessage();
    }
}
