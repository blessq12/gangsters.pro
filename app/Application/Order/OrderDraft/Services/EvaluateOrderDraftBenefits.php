<?php

namespace App\Application\Order\OrderDraft\Services;

use App\Application\Order\OrderDraft\Mapper\OrderDraftBenefitsInputMapper;
use App\Application\Order\OrderDraft\Support\CartRollCounter;
use App\Application\Order\OrderDraft\Support\PromotionLineClassifier;
use App\Application\Promotion\Services\EvaluatePromotionBenefits;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Order\Port\CatalogGiftCandidatesPort;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Shared\Enum\DeliveryMethod;

/**
 * Оркестрация расчёта benefits для ответа Checkout API.
 */
final class EvaluateOrderDraftBenefits
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
    public function evaluate(OrderDraft $draft): array
    {
        $address = $draft->delivery()?->address();

        return $this->promotionBenefits->evaluate(
            OrderDraftBenefitsInputMapper::map(
                currentKopecks: $draft->cart()->payableTotal()->amountRubles() * 100,
                orderChannel: $this->resolveOrderChannel($draft),
                deliveryMethod: $draft->delivery()?->method(),
                rollCount: $this->rollCounter->countRollUnits(
                    PromotionLineClassifier::userLines($draft->cart()->lines()),
                ),
                selectedGiftProductId: $this->resolveSelectedGiftProductId($draft),
                giftCandidates: $this->giftCandidates->listActiveGiftCandidates(),
                complementCandidates: $this->complementCandidates->listActiveComplementSetCandidates(),
                deliveryLatitude: $address?->latitude(),
                deliveryLongitude: $address?->longitude(),
            ),
        );
    }

    private function resolveOrderChannel(OrderDraft $draft): PromotionOrderChannel
    {
        $method = $draft->delivery()?->method();

        if ($method === DeliveryMethod::Courier) {
            return PromotionOrderChannel::Courier;
        }

        return PromotionOrderChannel::Pickup;
    }

    private function resolveSelectedGiftProductId(OrderDraft $draft): ?int
    {
        foreach ($draft->cart()->lines() as $line) {
            if (PromotionLineClassifier::isGiftLine($line)) {
                return $line->productId();
            }
        }

        return null;
    }
}
