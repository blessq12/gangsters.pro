<?php

namespace App\Application\MarketingContent\useCases;

use App\Application\MarketingContent\Presenter\MarketingContentPresenter;
use App\Domain\MarketingContent\Repository\BannerRepository;
use App\Domain\MarketingContent\Repository\PromotionRepository;

/**
 * Сценарий: получить публичный маркетинговый контент витрины.
 */
final class GetMarketingContentUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly PromotionRepository $promotions,
        private readonly MarketingContentPresenter $presenter,
    ) {}

    /**
     * @return array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        return $this->presenter->present(
            $this->banners->findActiveOrdered(),
            $this->promotions->findActiveOrdered(),
        );
    }
}
