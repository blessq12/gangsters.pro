<?php

namespace App\Application\Order\Checkout;

use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Domain\Supporting\Company\Enum\BranchMenuEntryKind;
use App\Domain\Supporting\Company\Repository\BranchCategoryRepository;
use DateTimeInterface;

/**
 * Единственная точка Application, которая применяет политики оформления.
 * Комплектные и подарки берутся из ролевых категорий филиала.
 */
final class FormCheckoutPromotions
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly OrderCatalogSnapshotPort $catalog,
        private readonly OrderBranchMenuSnapshotPort $menu,
        private readonly BranchCategoryRepository $categories,
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
            $availableMenu[(int) $item['catalog_item_id']] = $item;
        }

        $complementIds = $this->productIdsFromRoleCategory($branchId, 'complement');
        $giftIds = $this->productIdsFromRoleCategory($branchId, 'gift');

        $setIds = [];
        $productIds = [];
        foreach ($baseLines as $line) {
            if ($line->lineType() === OrderLineType::Set) {
                $setIds[] = $line->productId();
            } else {
                $productIds[$line->productId()] = $line->productId();
            }
        }
        foreach (array_keys($availableMenu) as $menuItemId) {
            $productIds[(int) $menuItemId] = (int) $menuItemId;
        }
        foreach ($complementIds as $id) {
            $productIds[$id] = $id;
        }
        foreach ($giftIds as $id) {
            $productIds[$id] = $id;
        }

        $sets = $setIds === []
            ? []
            : $this->menu->findAvailableSetsByBranchAndIds($branchId, array_values(array_unique($setIds)));

        foreach ($sets as $set) {
            foreach ($set['composition'] ?? [] as $part) {
                $partId = (int) ($part['product_id'] ?? 0);
                if ($partId > 0) {
                    $productIds[$partId] = $partId;
                }
            }
        }

        $products = [];
        if ($productIds !== []) {
            $products = $this->catalog->findActiveProductsByIds(array_values($productIds));
        }

        $isRollByProductId = [];
        foreach ($productIds as $id) {
            $product = $products[$id] ?? null;
            $isRollByProductId[$id] = $product !== null && ($product['is_roll'] ?? false);
        }

        /** @var array<int, int> $rollsPerUnitByLineId */
        $rollsPerUnitByLineId = [];
        foreach ($baseLines as $line) {
            $lineId = $line->productId();
            if ($line->lineType() === OrderLineType::Set) {
                $rolls = 0;
                foreach ($sets[$lineId]['composition'] ?? [] as $part) {
                    $partId = (int) ($part['product_id'] ?? 0);
                    $partQty = max(0, (int) ($part['quantity'] ?? 0));
                    if ($partId > 0 && ($isRollByProductId[$partId] ?? false)) {
                        $rolls += $partQty;
                    }
                }
                $rollsPerUnitByLineId[$lineId] = $rolls;
                continue;
            }
            $rollsPerUnitByLineId[$lineId] = ($isRollByProductId[$lineId] ?? false) ? 1 : 0;
        }

        $complements = [];
        foreach ($complementIds as $catalogItemId) {
            if (! isset($availableMenu[$catalogItemId])) {
                continue;
            }
            $product = $products[$catalogItemId] ?? null;
            if ($product === null) {
                continue;
            }
            $complements[] = [
                'product_id' => $product['id'],
                'name' => $product['name'],
            ];
        }

        $giftCandidates = [];
        foreach ($giftIds as $catalogItemId) {
            if (! isset($availableMenu[$catalogItemId])) {
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
            $names = $this->catalog->findProductNamesByIds([$catalogItemId]);
            $name = $names[$catalogItemId] ?? null;
            if ($name !== null && $name !== '') {
                $giftCandidates[] = [
                    'product_id' => $catalogItemId,
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
            rollsPerUnitByLineId: $rollsPerUnitByLineId,
        );
    }

    /**
     * @return list<int>
     */
    private function productIdsFromRoleCategory(int $branchId, string $role): array
    {
        $category = null;
        foreach ($this->categories->listActiveOrderedByBranch($branchId) as $row) {
            if ($role === 'complement' && $row->isComplement()) {
                $category = $row;
                break;
            }
            if ($role === 'gift' && $row->isGift()) {
                $category = $row;
                break;
            }
        }
        if ($category === null) {
            return [];
        }

        $ids = [];
        foreach ($this->categories->findItemsByCategoryId($category->id()) as $item) {
            if ($item->entryKind() !== BranchMenuEntryKind::Product) {
                continue;
            }
            $ids[] = $item->entryId();
        }

        return array_values(array_unique($ids));
    }
}
