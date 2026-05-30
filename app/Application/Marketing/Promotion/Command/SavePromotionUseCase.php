<?php

namespace App\Application\Marketing\Promotion\Command;

use App\Application\Marketing\Promotion\DTO\SavePromotionDTO;
use App\Application\Marketing\Promotion\Presenter\AdminPromotionPresenter;
use App\Domain\SystemContent\Entity\Promotion;
use App\Domain\SystemContent\Repository\PromotionRepository;

final class SavePromotionUseCase
{
    public function __construct(
        private readonly PromotionRepository $promotions,
        private readonly AdminPromotionPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SavePromotionDTO $dto): array
    {
        $promotion = new Promotion(
            id: $dto->id,
            title: $dto->title,
            description: $dto->description,
            imagePath: $dto->image,
        );

        $saved = $this->promotions->save($promotion);

        return $this->presenter->presentDetail($saved);
    }
}
