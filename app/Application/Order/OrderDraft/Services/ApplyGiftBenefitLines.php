<?php

namespace App\Application\Order\OrderDraft\Services;

use App\Application\Order\OrderDraft\Support\PromotionLineClassifier;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\OrderDraft\Exception\OrderDraftGiftBenefitViolationException;
use App\Domain\Order\Port\CatalogGiftCandidatesPort;
use App\Domain\Order\Port\CatalogPricingPort;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\CartSnapshot;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\PromotionOrderChannel;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\ValueObject\GiftBenefitRule;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\ValueObject\Money;

/**
 * Синхронизирует строку подарка: price=0 при eligibility, удаляет при потере условия.
 */
final class ApplyGiftBenefitLines
{
    public function __construct(
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly CatalogGiftCandidatesPort $giftCandidates,
        private readonly CatalogPricingPort $pricing,
    ) {}

    public function sync(OrderDraft $draft): void
    {
        $lines = $draft->cart()->lines();
        $giftProductId = $this->resolveSelectedGiftProductId($lines);
        $eligible = $this->isEligible($draft);
        $baseLines = PromotionLineClassifier::linesWithoutGift($lines);

        if (! $eligible || $giftProductId === null) {
            if ($giftProductId !== null) {
                $draft->setCart(CartSnapshot::fromLines($baseLines));
            }

            return;
        }

        if (! $this->isAllowedCandidate($giftProductId)) {
            $draft->setCart(CartSnapshot::fromLines($baseLines));

            return;
        }

        $giftLine = $this->buildGiftLine($giftProductId);

        if ($giftLine === null) {
            $draft->setCart(CartSnapshot::fromLines($baseLines));

            return;
        }

        $draft->setCart(CartSnapshot::fromLines([...$baseLines, $giftLine]));
    }

    public function assertValidForPlace(OrderDraft $draft): void
    {
        $giftLines = array_values(array_filter(
            $draft->cart()->lines(),
            static fn (CartLineSnapshot $line): bool => PromotionLineClassifier::isGiftLine($line),
        ));

        if ($giftLines === []) {
            return;
        }

        if (count($giftLines) > 1) {
            throw OrderDraftGiftBenefitViolationException::invalidGiftLine();
        }

        $giftLine = $giftLines[0];

        if ($giftLine->quantity() !== 1 || $giftLine->unitPrice()->amountRubles() !== 0) {
            throw OrderDraftGiftBenefitViolationException::invalidGiftLine();
        }

        if (! $this->isEligible($draft)) {
            throw OrderDraftGiftBenefitViolationException::notEligible();
        }

        if (! $this->isAllowedCandidate($giftLine->productId())) {
            throw OrderDraftGiftBenefitViolationException::invalidCandidate();
        }
    }

    private function isEligible(OrderDraft $draft): bool
    {
        $rule = $this->resolveGiftRule($draft);

        if (! $rule instanceof GiftBenefitRule || ! $rule->isActive()) {
            return false;
        }

        $payableKopecks = $draft->cart()->payableTotal()->amountRubles() * 100;

        return $payableKopecks > $rule->minOrderAmountKopecks()
            && $this->giftCandidates->listActiveGiftCandidates() !== [];
    }

    private function resolveGiftRule(OrderDraft $draft): ?GiftBenefitRule
    {
        $policy = $this->promotionPolicies->find();

        if (! $policy instanceof PromotionPolicy) {
            return null;
        }

        return $policy->giftRuleForChannel($this->resolveOrderChannel($draft));
    }

    private function resolveOrderChannel(OrderDraft $draft): PromotionOrderChannel
    {
        if ($draft->delivery()?->method() === DeliveryMethod::Courier) {
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
