<?php

namespace App\Domain\Client\VO;

use InvalidArgumentException;

final class PhoneNumber
{
    public function __construct(
        private string $value,
    ) {
        $normalized = preg_replace('/\D+/', '', $this->value ?? '');

        if ($normalized === null || $normalized === '' || strlen($normalized) < 6) {
            throw new InvalidArgumentException('Invalid phone number');
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

