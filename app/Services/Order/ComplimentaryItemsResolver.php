<?php

namespace App\Services\Order;

use App\Domain\Product\Entity\Product;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Services\Order\Complimentary\ComplimentaryPolicyInterface;

class ComplimentaryItemsResolver
{
    /**
     * @param array<int, ComplimentaryPolicyInterface> $policies
     */
    public function __construct(
        private readonly array $policies,
    ) {
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array<int, array{
     *     productOriginalId: int|null,
     *     name: string,
     *     sku: string,
     *     listPrice: int,
     *     finalPrice: int,
     *     quantity: int,
     *     attributes: array,
     *     media: array
     * }>
     */
    public function resolveForOrderItemsData(array $items): array
    {
        $cartProductIds = $this->extractProductIds($items);
        if ($cartProductIds === []) {
            return [];
        }

        $ruleMatches = $this->resolveRuleMatches($cartProductIds);
        if ($ruleMatches === []) {
            return [];
        }

        $rulesByGiftId = [];
        foreach ($ruleMatches as $rule) {
            $giftProductId = (int) $rule['gift_product_id'];
            if (!isset($rulesByGiftId[$giftProductId])) {
                $rulesByGiftId[$giftProductId] = $rule;
            }
        }

        $giftProducts = PRD_Product::with('images')
            ->whereIn('id', array_keys($rulesByGiftId))
            ->where('status', Product::STATUS_ACTIVE)
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($rulesByGiftId as $giftProductId => $rule) {
            /** @var PRD_Product|null $gift */
            $gift = $giftProducts->get($giftProductId);
            if ($gift === null) {
                continue;
            }

            $price = $gift->price !== null ? (int) $gift->price : null;
            if ($price === null || $price <= 0) {
                continue;
            }

            $result[] = [
                'productOriginalId' => (int) $gift->id,
                'name' => (string) $gift->name,
                'sku' => $gift->articul ?: (string) $gift->id,
                'listPrice' => $price,
                'finalPrice' => 0,
                'quantity' => 1,
                'attributes' => [
                    'is_complimentary' => true,
                    'source_rule_id' => (int) $rule['rule_id'],
                ],
                'media' => $this->modelImagesToMedia($gift),
            ];
        }

        return $result;
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array<int, array{
     *     rule_id: int,
     *     product_id: int,
     *     name: string,
     *     quantity: int,
     *     list_price: int
     * }>
     */
    public function resolvePreview(array $items): array
    {
        $resolvedItems = $this->resolveForOrderItemsData($items);

        return array_map(static fn (array $row): array => [
            'rule_id' => (int) ($row['attributes']['source_rule_id'] ?? 0),
            'product_id' => (int) $row['productOriginalId'],
            'name' => (string) $row['name'],
            'quantity' => (int) $row['quantity'],
            'list_price' => (int) $row['listPrice'],
        ], $resolvedItems);
    }

    /**
     * @param array<int, int> $cartProductIds
     * @return array<int, array{rule_id: int, trigger_category_id: int, gift_product_id: int, priority: int}>
     */
    private function resolveRuleMatches(array $cartProductIds): array
    {
        $matches = [];
        foreach ($this->policies as $policy) {
            foreach ($policy->resolve($cartProductIds) as $rule) {
                $matches[] = $rule;
            }
        }

        return $matches;
    }

    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     * @return array<int, int>
     */
    private function extractProductIds(array $items): array
    {
        $ids = [];
        foreach ($items as $row) {
            $id = (int) ($row['product_id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function modelImagesToMedia(PRD_Product $product): array
    {
        $out = [];
        foreach ($product->images as $img) {
            if ($img->thumb_path) {
                $out[] = ['url' => $img->thumb_path, 'variant' => 'thumb'];
            }
            if ($img->medium_path) {
                $out[] = ['url' => $img->medium_path, 'variant' => 'medium'];
            }
            if ($img->large_path) {
                $out[] = ['url' => $img->large_path, 'variant' => 'large'];
            }
        }

        return $out;
    }
}
