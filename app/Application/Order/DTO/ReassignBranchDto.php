<?php

namespace App\Application\Order\DTO;

final readonly class ReassignBranchDto
{
    public function __construct(
        public int $orderId,
        public int $branchId,
    ) {}
}
