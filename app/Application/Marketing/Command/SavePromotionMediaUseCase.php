<?php

namespace App\Application\Marketing\Command;

use App\Application\Marketing\DTO\SavePromotionMediaDto;
use App\Application\Marketing\Presenter\MarketingPresenter;
use App\Domain\Marketing\Repository\PromotionRepository;

final class SavePromotionMediaUseCase
{
    public function __construct(
        private readonly PromotionRepository $promotions,
        private readonly MarketingPresenter $presenter,
    ) {}

    /**
     * @return array{promotion: array<string, mixed>, created: bool}
     */
    public function execute(SavePromotionMediaDto $dto): array
    {
        $title = trim($dto->title);
        if ($title === '') {
            throw new \InvalidArgumentException('Заголовок обязателен.');
        }

        $body = trim((string) ($dto->body ?? ''));
        $body = $body !== '' ? $body : null;
        $image = trim((string) ($dto->image ?? ''));
        $image = $image !== '' ? $image : null;

        $promotion = $this->promotions->save(
            id: $dto->id,
            title: $title,
            body: $body,
            isActive: $dto->isActive,
            imagePath: $image,
        );

        return [
            'promotion' => $this->presenter->promotion($promotion),
            'created' => $dto->id === null,
        ];
    }
}
