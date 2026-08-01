<?php

namespace App\Application\Content\useCases;

use App\Application\Content\Presenter\MarketingContentPresenter;
use App\Domain\Content\Repository\BannerRepository;
use App\Domain\Content\Repository\PromotionRepository;

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
     * @return array{banners: list<array<string, mixed>>}
     */
    public function executeBannersOnly(): array
    {
        return [
            'banners' => $this->presenter->presentBanners(
                $this->banners->findActiveOrdered(),
            ),
        ];
    }

    /**
     * @return array{promotions: list<array<string, mixed>>}
     */
    public function executePromotionsOnly(): array
    {
        return [
            'promotions' => $this->presenter->presentPromotions(
                $this->promotions->findActiveOrdered(),
            ),
        ];
    }

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
