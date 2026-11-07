<?php

namespace App\Application\Content\Command;

use App\Domain\Content\Repository\PromotionRepository;

final class SaveStorePromotionUseCase
{
    public function __construct(
        private readonly PromotionRepository $promotions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(?int $id, string $title, ?string $body, bool $isActive, ?string $imagePath): array
    {
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('Нужен заголовок акции.');
        }

        $body = trim((string) $body);
        $promotion = $this->promotions->save($id, $title, $body !== '' ? $body : null, $isActive, $imagePath);

        return [
            'id' => $promotion->id(),
            'title' => $promotion->title(),
            'body' => $promotion->body() ?? '',
            'image' => $promotion->image(),
            'is_active' => $promotion->isActive(),
        ];
    }
}
