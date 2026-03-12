<?php

namespace App\Domain\Client\VO;

use InvalidArgumentException;

final class Email
{
    public function __construct(
        private string $value,
    ) {
        $normalized = mb_strtolower(trim($this->value));

        if ($normalized === '' || !filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

