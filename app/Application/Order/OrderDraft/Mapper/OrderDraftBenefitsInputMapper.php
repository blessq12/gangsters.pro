<?php

namespace App\Application\Order\OrderDraft\Mapper;

use App\Application\Promotion\DTO\PromotionBenefitsInput;
use App\Domain\Order\Port\CatalogComplementSetCandidate;
use App\Domain\Order\Port\CatalogGiftCandidate;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\ValueObject\BenefitProductCandidate;
use App\Shared\Enum\DeliveryMethod;

final class OrderDraftBenefitsInputMapper
{
    /**
     * @param  list<CatalogGiftCandidate>  $giftCandidates
     * @param  list<CatalogComplementSetCandidate>  $complementCandidates
     */
    public static function map(
        int $currentKopecks,
        PromotionOrderChannel $orderChannel,
        ?DeliveryMethod $deliveryMethod,
        int $rollCount,
        ?int $selectedGiftProductId,
        array $giftCandidates,
        array $complementCandidates,
        ?float $deliveryLatitude = null,
        ?float $deliveryLongitude = null,
    ): PromotionBenefitsInput {
        return new PromotionBenefitsInput(
            currentKopecks: $currentKopecks,
            orderChannel: $orderChannel,
            deliveryMethod: $deliveryMethod,
            rollCount: $rollCount,
            selectedGiftProductId: $selectedGiftProductId,
            giftCandidates: array_map(
                static fn (CatalogGiftCandidate $candidate): BenefitProductCandidate => new BenefitProductCandidate(
                    productId: $candidate->productId(),
                    productName: $candidate->productName(),
                    priceRubles: $candidate->priceRubles(),
                    imageUrl: $candidate->imageUrl(),
                ),
                $giftCandidates,
            ),
            complementCandidates: array_map(
                static fn (CatalogComplementSetCandidate $candidate): BenefitProductCandidate => new BenefitProductCandidate(
                    productId: $candidate->productId(),
                    productName: $candidate->productName(),
                    priceRubles: $candidate->priceRubles(),
                    imageUrl: $candidate->imageUrl(),
                ),
                $complementCandidates,
            ),
            deliveryLatitude: $deliveryLatitude,
            deliveryLongitude: $deliveryLongitude,
        );
    }
}
