<?php

namespace App\Application\Checkout\Services;

use App\Application\Promotion\Services\EvaluatePromotionBenefits;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Checkout\Port\CatalogGiftCandidatesPort;
use App\Domain\Checkout\Port\CatalogRollMetaPort;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Promotion\Enum\PromotionOrderChannel;

/**
 * Оркестрация расчёта benefits для ответа Checkout API.
 */
final class EvaluateCheckoutBenefits
{
    public function __construct(
        private readonly EvaluatePromotionBenefits $promotionBenefits,
        private readonly CatalogGiftCandidatesPort $giftCandidates,
        private readonly CatalogComplementSetCandidatesPort $complementCandidates,
        private readonly CatalogRollMetaPort $rollMeta,
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
        return $this->promotionBenefits->evaluate(
            currentKopecks: $this->payableTotalKopecks($checkout),
            orderChannel: $this->resolveOrderChannel($checkout),
            deliveryMethod: $checkout->delivery()?->method(),
            rollCount: $this->countRollUnitsInCart($checkout),
            selectedGiftProductId: $this->resolveSelectedGiftProductId($checkout),
            giftCandidates: $this->giftCandidates->listActiveGiftCandidates(),
            complementCandidates: $this->complementCandidates->listActiveComplementSetCandidates(),
        );
    }

    private function countRollUnitsInCart(Checkout $checkout): int
    {
        $userLines = array_values(array_filter(
            $checkout->cart()->lines(),
            fn (CartLineSnapshot $line): bool => ! $this->isPromotionSystemLine($line),
        ));

        if ($userLines === []) {
            return 0;
        }

        $productIds = array_map(
            static fn (CartLineSnapshot $line): int => $line->productId(),
            $userLines,
        );

        $countsAsRollByProductId = $this->rollMeta->countsAsRollByProductIds($productIds);
        $rollCount = 0;

        foreach ($userLines as $line) {
            if (! ($countsAsRollByProductId[$line->productId()] ?? false)) {
                continue;
            }

            $rollCount += $line->quantity();
        }

        return $rollCount;
    }

    private function payableTotalKopecks(Checkout $checkout): int
    {
        $totalRubles = 0;

        foreach ($checkout->cart()->lines() as $line) {
            if ($this->isPromotionSystemLine($line)) {
                continue;
            }

            $totalRubles += $line->lineTotal()->amountRubles();
        }

        return $totalRubles * 100;
    }

    private function isGiftLine(CartLineSnapshot $line): bool
    {
        $payload = $line->payload();

        return is_array($payload) && (($payload['kind'] ?? null) === 'gift');
    }

    private function isComplementLine(CartLineSnapshot $line): bool
    {
        $payload = $line->payload();

        return is_array($payload) && (($payload['kind'] ?? null) === 'complement');
    }

    private function isPromotionSystemLine(CartLineSnapshot $line): bool
    {
        return $this->isGiftLine($line) || $this->isComplementLine($line);
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
            if (! $this->isGiftLine($line)) {
                continue;
            }

            return $line->productId();
        }

        return null;
    }
}
