<?php

namespace App\Domain\YandexFood\Exception;

use RuntimeException;

final class YandexFoodOAuthRejectedException extends RuntimeException
{
    public function __construct(
        private readonly string $description,
    ) {
        parent::__construct($description);
    }

    public function description(): string
    {
        return $this->description;
    }
}
