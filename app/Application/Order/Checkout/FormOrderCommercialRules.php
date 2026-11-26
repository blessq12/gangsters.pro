<?php

namespace App\Application\Order\Checkout;

use App\Domain\Core\Order\Entity\OrderCommercialRules;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\Port\OrderBranchCommercialRulesPort;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeInterface;

/**
 * Единственная точка Application, которая применяет коммерческие правила оформления.
 * Комплектные и подарки — через порт меню своего BC.
 */
final class FormOrderCommercialRules
{
    public function __construct(
        private readonly OrderBranchCommercialRulesPort $commercialRules,
        private readonly OrderCatalogSnapshotPort $catalog,
        private readonly OrderBranchMenuSnapshotPort $menu,
    ) {}

    /**
     * @param  list<OrderLine>  $baseLines
     * @param  array<int, int>  $complementSelections  productId → желаемое кол-во; пусто = все комплекты по максимуму
     * @return array{
     *     lines: list<OrderLine>,
     *     delivery: OrderDeliveryInfo,
     *     rules: OrderCommercialRules,
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
        array $complementSelections = [],
    ): array {
        $rules = $this->commercialRules->findByBranch($branchId);
        if ($rules === null) {
            throw new \InvalidArgumentException('Коммерческие правила филиала не найдены.');
        }

        $facts = $this->factsFor($branchId, $baseLines, $giftProductId, $complementSelections);

        $formed = OrderFormation::apply(
            baseLines: $baseLines,
            delivery: $delivery,
            rules: $rules,
            facts: $facts,
            channel: $delivery->isPickup() ? 'pickup' : 'courier',
            at: $at,
            timezone: (string) config('app.timezone', 'UTC'),
            zoneIsRemote: $zoneIsRemote,
        );

        return [
            'lines' => $formed['lines'],
            'delivery' => $formed['delivery'],
            'rules' => $rules,
            'facts' => $facts,
        ];
    }

    /**
     * Комплектные и кандидаты в подарок филиала (доступные в меню).
     *
     * @return array{
     *     complements: list<array{product_id: int, name: string}>,
     *     gift_candidates: list<array{product_id: int, name: string}>
     * }
     */
    public function commercialCatalog(int $branchId): array
    {
        $availableMenu = [];
        foreach ($this->menu->listAvailableByBranch($branchId) as $item) {
            $availableMenu[(int) $item['catalog_item_id']] = $item;
        }

        $complementIds = $this->productIdsFromRoleCategory($branchId, 'complement');
        $giftIds = $this->productIdsFromRoleCategory($branchId, 'gift');
        $ids = array_values(array_unique([...$complementIds, ...$giftIds]));
        $products = $ids === [] ? [] : $this->catalog->findActiveProductsByIds($ids);

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

        return [
            'complements' => $complements,
            'gift_candidates' => $giftCandidates,
        ];
    }

    /**
     * @param  list<OrderLine>  $baseLines
     * @param  array<int, int>  $complementSelections
     */
    private function factsFor(
        int $branchId,
        array $baseLines,
        ?int $giftProductId,
        array $complementSelections,
    ): OrderFormationFacts {
        $availableMenu = [];
        foreach ($this->menu->listAvailableByBranch($branchId) as $item) {
            $availableMenu[(int) $item['catalog_item_id']] = $item;
        }

        $commercialCatalog = $this->commercialCatalog($branchId);

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
        foreach ([...$commercialCatalog['complements'], ...$commercialCatalog['gift_candidates']] as $promoProduct) {
            $productIds[$promoProduct['product_id']] = $promoProduct['product_id'];
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

        $giftCandidates = $commercialCatalog['gift_candidates'];

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
            complements: $commercialCatalog['complements'],
            giftCandidates: $giftCandidates,
            gift: $gift,
            rollsPerUnitByLineId: $rollsPerUnitByLineId,
            complementSelections: $complementSelections,
        );
    }

    /**
     * @return list<int>
     */
    private function productIdsFromRoleCategory(int $branchId, string $role): array
    {
        return $this->menu->listProductIdsByRole($branchId, $role);
    }
}
