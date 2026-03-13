<?php

namespace App\Domain\Product\VO;

final class CustomerStatus
{
    public function __construct(
        private readonly string $code, // regular | vip | corporate | ...
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }
}

