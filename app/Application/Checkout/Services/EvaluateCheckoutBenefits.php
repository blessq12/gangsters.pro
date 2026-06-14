<?php

namespace App\Application\Checkout\Services;

use App\Application\Checkout\Mapper\PromotionBenefitsInputMapper;
use App\Application\Checkout\Support\CartRollCounter;
use App\Application\Checkout\Support\PromotionLineClassifier;
use App\Application\Promotion\Services\EvaluatePromotionBenefits;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Checkout\Port\CatalogGiftCandidatesPort;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Shared\Enum\DeliveryMethod;

/**
 * Оркестрация расчёта benefits для ответа Checkout API.
 */
final class EvaluateCheckoutBenefits
{
    public function __construct(
        private readonly EvaluatePromotionBenefits $promotionBenefits,
        private readonly CatalogGiftCandidatesPort $giftCandidates,
        private readonly CatalogComplementSetCandidatesPort $complementCandidates,
        private readonly CartRollCounter $rollCounter,
    ) {}

    /**
     * @return array{
     *     benefits_progress: array<string, mixed>,
     *     delivery_pricing: array<string, mixed>|null,
     *     promo_state: array<string, mixed>
     * }
     */
    public function evaluate(Checkout $checkout): array
    {
        $address = $checkout->delivery()?->address();

        return $this->promotionBenefits->evaluate(
            PromotionBenefitsInputMapper::map(
                currentKopecks: $checkout->cart()->payableTotal()->amountRubles() * 100,
                orderChannel: $this->resolveOrderChannel($checkout),
                deliveryMethod: $checkout->delivery()?->method(),
                rollCount: $this->rollCounter->countRollUnits(
                    PromotionLineClassifier::userLines($checkout->cart()->lines()),
                ),
                selectedGiftProductId: $this->resolveSelectedGiftProductId($checkout),
                giftCandidates: $this->giftCandidates->listActiveGiftCandidates(),
                complementCandidates: $this->complementCandidates->listActiveComplementSetCandidates(),
                deliveryLatitude: $address?->latitude(),
                deliveryLongitude: $address?->longitude(),
            ),
        );
    }

    private function resolveOrderChannel(Checkout $checkout): PromotionOrderChannel
    {
        $method = $checkout->delivery()?->method();

        if ($method === DeliveryMethod::Courier) {
            return PromotionOrderChannel::Courier;
        }

        return PromotionOrderChannel::Pickup;
    }

    private function resolveSelectedGiftProductId(Checkout $checkout): ?int
    {
        foreach ($checkout->cart()->lines() as $line) {
            if (PromotionLineClassifier::isGiftLine($line)) {
                return $line->productId();
            }
        }

        return null;
    }
}
