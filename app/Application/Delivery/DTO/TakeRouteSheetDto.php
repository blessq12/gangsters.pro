<?php

namespace App\Application\Delivery\DTO;

final readonly class TakeRouteSheetDto
{
    public function __construct(
        public int $sheetId,
        public int $courierId,
    ) {}
}
