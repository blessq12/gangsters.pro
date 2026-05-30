<?php

namespace App\Application\Catalog\DTO;

final readonly class UpdateCartRuleFlagsDTO
{
    public function __construct(
        public int $productId,
        public bool $countsAsRoll,
        public bool $giftCandidate,
        public bool $isComplementSet,
    ) {
    }
}
