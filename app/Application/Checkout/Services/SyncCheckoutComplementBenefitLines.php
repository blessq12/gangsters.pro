<?php

namespace App\Application\Checkout\Services;

use App\Application\Promotion\Services\ResolveComplementSetEntitlement;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Port\CatalogComplementSetCandidate;
use App\Domain\Checkout\Port\CatalogComplementSetCandidatesPort;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\Port\CatalogRollMetaPort;
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
        private readonly CatalogRollMetaPort $rollMeta,
        private readonly CatalogComplementSetCandidatesPort $complementCandidates,
        private readonly CatalogPricingPort $pricing,
    ) {}

    public function sync(Checkout $checkout): void
    {
        $userLines = $this->userCartLines($checkout);
        $candidates = $this->complementCandidates->listActiveComplementSetCandidates();
        $entitledSetCount = $this->complementEntitlement->resolve(
            $this->countRollUnits($userLines),
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

    /**
     * @param  list<CartLineSnapshot>  $userLines
     */
    private function countRollUnits(array $userLines): int
    {
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

    /**
     * @return list<CartLineSnapshot>
     */
    private function userCartLines(Checkout $checkout): array
    {
        return array_values(array_filter(
            $checkout->cart()->lines(),
            fn (CartLineSnapshot $line): bool => ! $this->isPromotionSystemLine($line),
        ));
    }

    private function isPromotionSystemLine(CartLineSnapshot $line): bool
    {
        $payload = $line->payload();

        if (! is_array($payload)) {
            return false;
        }

        $kind = $payload['kind'] ?? null;

        return $kind === 'gift' || $kind === 'complement';
    }
}
