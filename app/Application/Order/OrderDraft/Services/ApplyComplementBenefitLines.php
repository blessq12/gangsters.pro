<?php

namespace App\Application\Order\OrderDraft\Services;

use App\Application\Promotion\Services\ResolveComplementSetEntitlement;
use App\Domain\Order\OrderDraft\Entity\OrderDraft;
use App\Domain\Order\Port\CatalogComplementSetCandidate;
use App\Domain\Order\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Order\Port\CatalogPricingPort;
use App\Application\Order\OrderDraft\Support\CartRollCounter;
use App\Application\Order\OrderDraft\Support\PromotionLineClassifier;
use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;
use App\Domain\Order\OrderDraft\ValueObject\CartSnapshot;
use App\Shared\ValueObject\Money;

/**
 * Синхронизирует автоматические строки комплекта дополнений в корзине checkout.
 */
final class ApplyComplementBenefitLines
{
    public function __construct(
        private readonly ResolveComplementSetEntitlement $complementEntitlement,
        private readonly CartRollCounter $rollCounter,
        private readonly CatalogComplementSetCandidatesPort $complementCandidates,
        private readonly CatalogPricingPort $pricing,
    ) {}

    public function sync(OrderDraft $draft): void
    {
        $userLines = PromotionLineClassifier::userLines($draft->cart()->lines());
        $candidates = $this->complementCandidates->listActiveComplementSetCandidates();
        $entitledSetCount = $this->complementEntitlement->resolve(
            $this->rollCounter->countRollUnits($userLines),
            $candidates !== [],
        );
        $lines = $userLines;

        if ($entitledSetCount > 0) {
            foreach ($candidates as $candidate) {
                $line = $this->buildComplementLine($candidate, $entitledSetCount);

                if ($line instanceof CartLineSnapshot) {
                    $lines[] = $line;
                }
            }
        }

        $draft->setCart(CartSnapshot::fromLines($lines));
    }

    private function buildComplementLine(
        CatalogComplementSetCandidate $candidate,
        int $entitledSetCount,
    ): ?CartLineSnapshot {
        $quote = $this->pricing->findActiveProductQuote($candidate->productId());

        if ($quote === null) {
            return null;
        }

        return new CartLineSnapshot(
            productId: $quote->productId(),
            productName: $quote->productName(),
            quantity: $entitledSetCount,
            unitPrice: Money::zero(),
            payload: ['kind' => 'complement'],
        );
    }
}
