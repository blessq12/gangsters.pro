<?php

namespace App\Application\Promotion\DTO;

final readonly class AssignProductPromotionRoleDto
{
    public function __construct(
        public int $productId,
        public string $role,
        public string $name,
        public int $priceKopecks,
        public ?int $paidTwinProductId = null,
    ) {}
}
