<?php

namespace App\Domain\YandexFood\Exception;

use RuntimeException;

final class YandexFoodBearerTokenRejectedException extends RuntimeException
{
    private const REASON = 'Access token has been expired. You should request a new one';

    public function __construct()
    {
        parent::__construct(self::REASON);
    }

    public function reason(): string
    {
        return self::REASON;
    }
}
