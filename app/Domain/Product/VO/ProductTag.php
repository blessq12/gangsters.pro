<?php

namespace App\Domain\Product\VO;

final class ProductTag
{
    public function __construct(
        private readonly string $code,  // spicy, no_onion, kids_friendly и т.п.
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }
}

