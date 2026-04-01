<?php

namespace App\Domain\Product\VO;

final class ProductTag
{
    public function __construct(
        private readonly string $code,
        private readonly ?string $label = null,
        private readonly ?string $color = null,
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label ?: $this->code;
    }

    public function color(): string
    {
        return $this->color ?: 'amber';
    }
}

