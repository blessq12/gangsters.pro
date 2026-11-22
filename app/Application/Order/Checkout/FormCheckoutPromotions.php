<?php

namespace App\Application\Order\Checkout;

use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeInterface;

/**
 * Единственная точка Application, которая применяет политики оформления.
 * Правила живут в OrderFormation; флаги и подарок собираются здесь через порты Order.
 */
final class FormCheckoutPromotions
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly OrderCatalogSnapshotPort $catalog,
        private readonly OrderBranchMenuSnapshotPort $menu,
    ) {}

    /**
     * @param  list<OrderLine>  $baseLines
     * @return array{
     *     lines: list<OrderLine>,
     *     delivery: OrderDeliveryInfo,
     *     policy: \App\Domain\Core\Order\Policy\PromotionPolicy,
     *     facts: OrderFormationFacts
     * }
     */
    public function apply(
        int $branchId,
        array $baseLines,
        OrderDeliveryInfo $delivery,
        DateTimeInterface $at,
        bool $zoneIsRemote,
        ?int $giftProductId = null,
    ): array {
        $policy = $this->policies->findByBranch($branchId);
        if ($policy === null) {
            throw new \InvalidArgumentException('Политика акций филиала не найдена.');
        }

        $facts = $this->factsFor($branchId, $baseLines, $giftProductId);

        $formed = OrderFormation::apply(
            baseLines: $baseLines,
            delivery: $delivery,
            policy: $policy,
            facts: $facts,
            channel: $delivery->isPickup() ? 'pickup' : 'courier',
            at: $at,
            timezone: (string) config('app.timezone', 'UTC'),
            zoneIsRemote: $zoneIsRemote,
        );

        return [
            'lines' => $formed['lines'],
            'delivery' => $formed['delivery'],
            'policy' => $policy,
            'facts' => $facts,
        ];
    }

    /**
     * @param  list<OrderLine>  $baseLines
     */
    private function factsFor(int $branchId, array $baseLines, ?int $giftProductId): OrderFormationFacts
    {
        $availableMenu = [];
        foreach ($this->menu->listAvailableByBranch($branchId) as $item) {
            $availableMenu[$item['catalog_item_id']] = $item;
        }

        $productIds = [];
        foreach ($baseLines as $line) {
            $productIds[$line->productId()] = $line->productId();
        }
        foreach (array_keys($availableMenu) as $menuItemId) {
            $productIds[(int) $menuItemId] = (int) $menuItemId;
        }

        $products = [];
        if ($productIds !== []) {
            $products = $this->catalog->findActiveProductsByIds(array_values($productIds));
        }

        $isRollByProductId = [];
        foreach ($baseLines as $line) {
            $product = $products[$line->productId()] ?? null;
            $isRollByProductId[$line->productId()] = $product !== null && ($product['is_roll'] ?? false);
        }

        $complements = [];
        foreach ($availableMenu as $catalogItemId => $menuItem) {
            $product = $products[$catalogItemId] ?? null;
            if ($product === null || ! ($product['is_complement'] ?? false)) {
                continue;
            }
            $complements[] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
            ];
        }

        $giftCandidates = [];
        foreach ($availableMenu as $catalogItemId => $menuItem) {
            if (! ($menuItem['is_gift_candidate'] ?? false)) {
                continue;
            }
            $product = $products[$catalogItemId] ?? null;
            if ($product !== null) {
                $giftCandidates[] = [
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                ];
                continue;
            }
            $names = $this->catalog->findProductNamesByIds([(int) $catalogItemId]);
            $name = $names[(int) $catalogItemId] ?? null;
            if ($name !== null && $name !== '') {
                $giftCandidates[] = [
                    'product_id' => (int) $catalogItemId,
                    'name' => $name,
                ];
            }
        }

        $gift = null;
        if ($giftProductId !== null && $giftProductId > 0) {
            foreach ($giftCandidates as $candidate) {
                if ((int) $candidate['product_id'] === $giftProductId) {
                    $gift = $candidate;
                    break;
                }
            }
        }

        return new OrderFormationFacts(
            isRollByProductId: $isRollByProductId,
            complements: $complements,
            giftCandidates: $giftCandidates,
            gift: $gift,
        );
    }
}
