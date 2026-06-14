<?php

namespace App\Application\Checkout\Services;

use App\Application\Promotion\Services\ResolveComplementSetEntitlement;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Port\CatalogComplementSetCandidate;
use App\Domain\Checkout\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Application\Checkout\Support\CartRollCounter;
use App\Application\Checkout\Support\PromotionLineClassifier;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\CartSnapshot;
use App\Shared\ValueObject\Money;

/**
 * Синхронизирует автоматические строки комплекта дополнений в корзине checkout.
 */
final class SyncCheckoutComplementBenefitLines
{
    public function __construct(
        private readonly ResolveComplementSetEntitlement $complementEntitlement,
        private readonly CartRollCounter $rollCounter,
        private readonly CatalogComplementSetCandidatesPort $complementCandidates,
        private readonly CatalogPricingPort $pricing,
    ) {}

    public function sync(Checkout $checkout): void
    {
        $userLines = PromotionLineClassifier::userLines($checkout->cart()->lines());
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

        $checkout->setCart(CartSnapshot::fromLines($lines));
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
