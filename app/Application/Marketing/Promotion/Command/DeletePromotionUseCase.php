<?php

namespace App\Application\Marketing\Promotion\Command;

use App\Domain\SystemContent\Repository\PromotionRepository;

final class DeletePromotionUseCase
{
    public function __construct(
        private readonly PromotionRepository $promotions,
    ) {
    }

    public function execute(int $id): void
    {
        $this->promotions->delete($id);
    }
}
