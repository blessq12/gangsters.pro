<?php

namespace App\Application\Order\Checkout;

use App\Domain\Core\Order\Policy\OrderFormation;
use App\Domain\Core\Order\Repository\PromotionPolicyRepository;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use DateTimeInterface;

/**
 * Единственная точка Application, которая применяет политики оформления.
 * Правила живут в OrderFormation; флаги и подарок собираются здесь из Catalog + меню.
 */
final class FormCheckoutPromotions
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly CatalogItemRepository $catalog,
        private readonly BranchMenuRepository $menu,
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
    ): array {
        $policy = $this->policies->findByBranch($branchId);
        if ($policy === null) {
            throw new \InvalidArgumentException('Политика акций филиала не найдена.');
        }

        $facts = $this->factsFor($branchId, $baseLines, $policy->giftProductId());

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
            $availableMenu[$item->catalogItemId()] = $item;
        }

        $productIds = [];
        foreach ($baseLines as $line) {
            $productIds[$line->productId()] = $line->productId();
        }
        foreach (array_keys($availableMenu) as $menuItemId) {
            $productIds[(int) $menuItemId] = (int) $menuItemId;
        }
        if ($giftProductId !== null && $giftProductId > 0) {
            $productIds[$giftProductId] = $giftProductId;
        }

        $products = [];
        if ($productIds !== []) {
            foreach ($this->catalog->findActiveProductsByIds(array_values($productIds)) as $product) {
                $products[$product->id()] = $product;
            }
        }

        $isRollByProductId = [];
        foreach ($baseLines as $line) {
            $product = $products[$line->productId()] ?? null;
            $isRollByProductId[$line->productId()] = $product !== null && $product->isRoll();
        }

        $complements = [];
        foreach ($availableMenu as $catalogItemId => $menuItem) {
            $product = $products[$catalogItemId] ?? null;
            if ($product === null || ! $product->isComplement()) {
                continue;
            }
            $complements[] = [
                'product_id' => $product->id(),
                'name' => $product->name(),
            ];
        }

        $gift = null;
        if ($giftProductId !== null && $giftProductId > 0 && isset($availableMenu[$giftProductId])) {
            $giftProduct = $products[$giftProductId] ?? null;
            if ($giftProduct !== null) {
                $gift = [
                    'product_id' => $giftProduct->id(),
                    'name' => $giftProduct->name(),
                ];
            } else {
                $names = $this->catalog->findProductNamesByIds([$giftProductId]);
                $name = $names[$giftProductId] ?? null;
                if ($name !== null && $name !== '') {
                    $gift = [
                        'product_id' => $giftProductId,
                        'name' => $name,
                    ];
                }
            }
        }

        return new OrderFormationFacts(
            isRollByProductId: $isRollByProductId,
            complements: $complements,
            gift: $gift,
        );
    }
}
