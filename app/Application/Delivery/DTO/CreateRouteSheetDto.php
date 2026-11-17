<?php

namespace App\Application\Delivery\DTO;

final readonly class CreateRouteSheetDto
{
    /**
     * @param  list<int>  $orderIds
     */
    public function __construct(
        public array $orderIds,
    ) {}
}
