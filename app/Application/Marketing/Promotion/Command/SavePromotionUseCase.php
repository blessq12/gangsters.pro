<?php

namespace App\Application\Marketing\Promotion\Command;

use App\Domain\SystemContent\Entity\Promotion;
use App\Domain\SystemContent\Repository\PromotionRepository;

final class SavePromotionUseCase
{
    public function __construct(
        private readonly PromotionRepository $promotions,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function execute(array $data): array
    {
        $promotion = new Promotion(
            id: (int) ($data['id'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            description: $data['description'] ?? null,
            imagePath: $data['image'] ?? null,
        );

        $saved = $this->promotions->save($promotion);

        return [
            'id' => $saved->id(),
            'title' => $saved->title(),
            'description' => $saved->description(),
            'image' => $saved->imagePath(),
        ];
    }
}
