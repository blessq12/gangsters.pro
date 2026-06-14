<?php

namespace App\Application\MarketingContent\useCases;

use App\Domain\MarketingContent\Entity\Banner;
use App\Domain\MarketingContent\Entity\Promotion;
use App\Domain\MarketingContent\Repository\BannerRepository;
use App\Domain\MarketingContent\Repository\PromotionRepository;
use App\Infrastructure\MarketingContent\Support\PublicMediaUrl;

/**
 * Сценарий: получить публичный маркетинговый контент витрины.
 */
final class GetMarketingContentUseCase
{
    public function __construct(
        private readonly BannerRepository $banners,
        private readonly PromotionRepository $promotions,
    ) {}

    /**
     * @return array{banners: list<array<string, mixed>>, promotions: list<array<string, mixed>>}
     */
    public function execute(): array
    {
        return [
            'banners' => array_map(
                fn (Banner $banner) => $this->mapBanner($banner),
                $this->banners->findActiveOrdered(),
            ),
            'promotions' => array_map(
                fn (Promotion $promotion) => $this->mapPromotion($promotion),
                $this->promotions->findActiveOrdered(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBanner(Banner $banner): array
    {
        $desktop = PublicMediaUrl::resolve($banner->imageDesktop());
        $mobile = PublicMediaUrl::resolve($banner->imageMobile());

        return [
            'id' => $banner->id(),
            'title' => $banner->title(),
            'description' => $banner->description(),
            'image_desktop' => $desktop,
            'image_mobile' => $mobile,
            'image' => $desktop ?? $mobile,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPromotion(Promotion $promotion): array
    {
        $body = $promotion->body();

        return [
            'id' => $promotion->id(),
            'title' => $promotion->title(),
            'image' => PublicMediaUrl::resolve($promotion->image()),
            'body' => $body,
            'description' => $this->plainTextExcerpt($body),
        ];
    }

    private function plainTextExcerpt(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) <= 240) {
            return $text;
        }

        return mb_substr($text, 0, 237).'…';
    }
}
