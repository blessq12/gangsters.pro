<?php

namespace App\Application\Order\Query;

use App\Application\Order\Checkout\CheckoutRulesVersion;
use App\Application\Order\Checkout\FormOrderCommercialRules;
use App\Domain\Core\Order\Port\OrderBranchCommercialRulesPort;
use App\Domain\Core\Order\Port\OrderBranchLookupPort;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\Port\OrderDeliveryTariffPort;

/**
 * Всё, по чему фронт сам формирует корзину тем же алгоритмом, что OrderFormation.
 */
final class GetCheckoutContextUseCase implements CheckoutRulesVersion
{
    public function __construct(
        private readonly OrderBranchLookupPort $branches,
        private readonly OrderBranchMenuSnapshotPort $menu,
        private readonly OrderCatalogSnapshotPort $catalog,
        private readonly OrderDeliveryTariffPort $deliveryTariffs,
        private readonly OrderBranchCommercialRulesPort $commercialRules,
        private readonly FormOrderCommercialRules $formation,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $branchId): array
    {
        $context = $this->build($branchId);

        return [
            'rules_version' => $this->hash($context),
            ...$context,
        ];
    }

    public function forBranch(int $branchId): string
    {
        return $this->hash($this->build($branchId));
    }

    /**
     * @return array<string, mixed>
     */
    private function build(int $branchId): array
    {
        $branch = $this->branches->requireActiveBranch($branchId);

        $rules = $this->commercialRules->findByBranch($branch['id']);
        if ($rules === null) {
            throw new \InvalidArgumentException('Коммерческие правила филиала не найдены.');
        }

        $deliveryConfig = $this->deliveryTariffs->findConfigByBranch($branch['id']);
        if ($deliveryConfig === null) {
            throw new \InvalidArgumentException('Конфигурация доставки филиала не найдена.');
        }

        $menuItems = $this->menu->listAvailableByBranch($branch['id']);
        $sets = $this->menu->listAvailableSetsByBranch($branch['id']);

        $productIds = [];
        foreach ($menuItems as $item) {
            $productIds[(int) $item['catalog_item_id']] = (int) $item['catalog_item_id'];
        }
        foreach ($sets as $set) {
            foreach ($set['composition'] as $part) {
                $productIds[(int) $part['product_id']] = (int) $part['product_id'];
            }
        }
        $products = $productIds === []
            ? []
            : $this->catalog->findActiveProductsByIds(array_values($productIds));

        $menuProducts = [];
        foreach ($menuItems as $item) {
            $product = $products[(int) $item['catalog_item_id']] ?? null;
            if ($product === null) {
                continue;
            }
            $menuProducts[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price_kopecks' => (int) $item['price_kopecks'],
                'rolls_per_unit' => ($product['is_roll'] ?? false) ? 1 : 0,
            ];
        }
        usort($menuProducts, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        $menuSets = [];
        foreach ($sets as $set) {
            $rolls = 0;
            foreach ($set['composition'] as $part) {
                $partProduct = $products[(int) $part['product_id']] ?? null;
                if ($partProduct !== null && ($partProduct['is_roll'] ?? false)) {
                    $rolls += max(0, (int) $part['quantity']);
                }
            }
            $menuSets[] = [
                'id' => $set['id'],
                'name' => $set['name'],
                'price_kopecks' => (int) $set['price_kopecks'],
                'rolls_per_unit' => $rolls,
            ];
        }
        usort($menuSets, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        $giftRules = [];
        foreach ($rules->giftRules() as $rule) {
            $giftRules[] = [
                'order_channel' => (string) ($rule['order_channel'] ?? ''),
                'min_order_amount_kopecks' => (int) ($rule['min_order_amount_kopecks'] ?? 0),
                'is_active' => (bool) ($rule['is_active'] ?? false),
                'allowed_weekdays' => array_values(array_map('intval', $rule['allowed_weekdays'] ?? [])),
            ];
        }
        $deliveryBenefit = $rules->deliveryBenefit();
        $complementBenefit = $rules->complementSetBenefit();
        $catalog = $this->formation->commercialCatalog($branch['id']);

        $zones = [];
        foreach ($deliveryConfig['zones'] as $zone) {
            $zones[] = [
                'id' => $zone['id'],
                'name' => $zone['name'],
                'fee_kopecks' => (int) $zone['fee_kopecks'],
                'is_remote' => (bool) $zone['is_remote'],
                'geometry' => $zone['geometry'],
            ];
        }

        return [
            'branch_id' => $branch['id'],
            'timezone' => (string) config('app.timezone', 'UTC'),
            'menu' => [
                'products' => $menuProducts,
                'sets' => $menuSets,
            ],
            'commercial_rules' => [
                'gift_rules' => $giftRules,
                'delivery_benefit' => [
                    'is_active' => (bool) ($deliveryBenefit['is_active'] ?? false),
                    'free_delivery_threshold_kopecks' => (int) ($deliveryBenefit['free_delivery_threshold_kopecks'] ?? 0),
                    'below_threshold_fee_mode' => (string) ($deliveryBenefit['below_threshold_fee_mode'] ?? 'base_tariff'),
                    'in_zone_at_threshold_fee_mode' => (string) ($deliveryBenefit['in_zone_at_threshold_fee_mode'] ?? 'free'),
                    'outside_zone_at_threshold_fee_mode' => (string) ($deliveryBenefit['outside_zone_at_threshold_fee_mode'] ?? 'outside_zone_surcharge_only'),
                ],
                'complement_set_benefit' => [
                    'is_active' => (bool) ($complementBenefit['is_active'] ?? false),
                    'rolls_per_set' => (int) ($complementBenefit['rolls_per_set'] ?? 1),
                    'max_sets' => (int) ($complementBenefit['max_sets'] ?? 0),
                ],
                'complements' => $catalog['complements'],
                'gift_candidates' => $catalog['gift_candidates'],
            ],
            'delivery' => [
                'min_order_kopecks' => $deliveryConfig['min_order_kopecks'],
                'zones' => $zones,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function hash(array $context): string
    {
        return sha1((string) json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }
}
