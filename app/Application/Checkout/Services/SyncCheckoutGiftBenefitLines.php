<?php

namespace App\Application\Checkout\Services;

use App\Application\Checkout\Support\PromotionLineClassifier;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Exception\CheckoutGiftBenefitViolationException;
use App\Domain\Checkout\Port\CatalogGiftCandidatesPort;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\CartSnapshot;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\ValueObject\Money;

/**
 * Синхронизирует строку подарка: price=0 при eligibility, удаляет при потере условия.
 */
final class SyncCheckoutGiftBenefitLines
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly CatalogGiftCandidatesPort $giftCandidates,
        private readonly CatalogPricingPort $pricing,
    ) {}

    public function sync(Checkout $checkout): void
    {
        $lines = $checkout->cart()->lines();
        $giftProductId = $this->resolveSelectedGiftProductId($lines);
        $eligible = $this->isEligible($checkout);
        $baseLines = PromotionLineClassifier::linesWithoutGift($lines);

        if (! $eligible || $giftProductId === null) {
            if ($giftProductId !== null) {
                $checkout->setCart(CartSnapshot::fromLines($baseLines));
            }

            return;
        }

        if (! $this->isAllowedCandidate($giftProductId)) {
            $checkout->setCart(CartSnapshot::fromLines($baseLines));

            return;
        }

        $giftLine = $this->buildGiftLine($giftProductId);

        if ($giftLine === null) {
            $checkout->setCart(CartSnapshot::fromLines($baseLines));

            return;
        }

        $checkout->setCart(CartSnapshot::fromLines([...$baseLines, $giftLine]));
    }

    public function assertValidForConfirm(Checkout $checkout): void
    {
        $giftLines = array_values(array_filter(
            $checkout->cart()->lines(),
            static fn (CartLineSnapshot $line): bool => PromotionLineClassifier::isGiftLine($line),
        ));

        if ($giftLines === []) {
            return;
        }

        if (count($giftLines) > 1) {
            throw CheckoutGiftBenefitViolationException::invalidGiftLine();
        }

        $giftLine = $giftLines[0];

        if ($giftLine->quantity() !== 1 || $giftLine->unitPrice()->amountRubles() !== 0) {
            throw CheckoutGiftBenefitViolationException::invalidGiftLine();
        }

        if (! $this->isEligible($checkout)) {
            throw CheckoutGiftBenefitViolationException::notEligible();
        }

        if (! $this->isAllowedCandidate($giftLine->productId())) {
            throw CheckoutGiftBenefitViolationException::invalidCandidate();
        }
    }

    private function isEligible(Checkout $checkout): bool
    {
        $rule = $this->resolveGiftRule($checkout);

        if (! $rule instanceof GiftBenefitRule || ! $rule->isActive()) {
            return false;
        }

        $payableKopecks = $checkout->cart()->payableTotal()->amountRubles() * 100;

        return $payableKopecks > $rule->minOrderAmountKopecks()
            && $this->giftCandidates->listActiveGiftCandidates() !== [];
    }

    private function resolveGiftRule(Checkout $checkout): ?GiftBenefitRule
    {
        $policy = $this->promotionPolicies->find();

        if (! $policy instanceof PromotionPolicy) {
            return null;
        }

        return $policy->giftRuleForChannel($this->resolveOrderChannel($checkout));
    }

    private function resolveOrderChannel(Checkout $checkout): PromotionOrderChannel
    {
        if ($checkout->delivery()?->method() === DeliveryMethod::Courier) {
            return PromotionOrderChannel::Courier;
        }

        return PromotionOrderChannel::Pickup;
    }

    private function isAllowedCandidate(int $productId): bool
    {
        foreach ($this->giftCandidates->listActiveGiftCandidates() as $candidate) {
            if ($candidate->productId() === $productId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<CartLineSnapshot>  $lines
     */
    private function resolveSelectedGiftProductId(array $lines): ?int
    {
        foreach ($lines as $line) {
            if (PromotionLineClassifier::isGiftLine($line)) {
                return $line->productId();
            }
        }

        return null;
    }

    private function buildGiftLine(int $productId): ?CartLineSnapshot
    {
        $quote = $this->pricing->findActiveProductQuote($productId);

        if ($quote === null) {
            return null;
        }

        return new CartLineSnapshot(
            productId: $quote->productId(),
            productName: $quote->productName(),
            quantity: 1,
            unitPrice: Money::zero(),
            payload: ['kind' => 'gift'],
        );
    }
}
