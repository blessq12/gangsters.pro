<?php

namespace App\Application\Marketing\Presenter;

use App\Domain\Marketing\Entity\Banner;
use App\Domain\Marketing\Entity\DeliveryConditionsText;
use App\Domain\Marketing\Entity\Promotion;

final class MarketingPresenter
{
    /**
     * @param  list<Banner>  $banners
     * @param  list<Promotion>  $promotions
     * @return array{
     *     banners: list<array<string, mixed>>,
     *     promotions: list<array<string, mixed>>,
     *     delivery_conditions_text: array<string, mixed>|null
     * }
     */
    public function present(
        array $banners,
        array $promotions,
        ?DeliveryConditionsText $deliveryConditions = null,
    ): array {
        return [
            'banners' => array_map(fn (Banner $banner): array => $this->banner($banner), $banners),
            'promotions' => array_map(fn (Promotion $promotion): array => $this->promotion($promotion), $promotions),
            'delivery_conditions_text' => $deliveryConditions !== null
                ? $this->deliveryConditions($deliveryConditions)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function banner(Banner $banner): array
    {
        return [
            'id' => $banner->id(),
            'image_desktop' => $banner->imageDesktop(),
            'image_mobile' => $banner->imageMobile(),
            'sort_order' => $banner->sortOrder(),
            'is_active' => $banner->isActive(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function promotion(Promotion $promotion): array
    {
        return [
            'id' => $promotion->id(),
            'title' => $promotion->title(),
            'body' => $promotion->body(),
            'image' => $promotion->image(),
            'sort_order' => $promotion->sortOrder(),
            'is_active' => $promotion->isActive(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function deliveryConditions(DeliveryConditionsText $text): array
    {
        return [
            'id' => $text->id(),
            'body' => $text->body(),
            'is_active' => $text->isActive(),
        ];
    }
}
