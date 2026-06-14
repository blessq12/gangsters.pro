<?php

namespace App\Application\Client\DTO;

final readonly class MergeGuestFavoritesDto
{
    /**
     * @param list<array{
     *     product_id: int,
     *     product_name?: ?string,
     *     price_rub?: ?float,
     *     weight?: ?string
     * }> $items
     */
    public function __construct(
        public int $clientId,
        public array $items,
    ) {}
}
