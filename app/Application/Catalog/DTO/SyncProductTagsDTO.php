<?php

namespace App\Application\Catalog\DTO;

final readonly class SyncProductTagsDTO
{
    /**
     * @param  string[]  $tagCodes
     */
    public function __construct(
        public int $productId,
        public array $tagCodes,
    ) {
    }
}
