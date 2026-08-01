<?php

namespace App\Application\Order\useCases;

use App\Application\Order\DTO\QuoteOrderDto;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Order\Port\PromotionDeliveryPricingPort;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use Illuminate\Support\Facades\Storage;

/**
 * Статичный quote: цены каталога + правила акций → snapshot для place.
 */
final class QuoteOrderUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $catalogItems,
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly PromotionDeliveryPricingPort $deliveryPricing,
    ) {}

    /**
     * @return array{
     *     cart: array<string, mixed>,
     *     client: array<string, mixed>,
     *     delivery: array<string, mixed>,
     *     payment: array<string, mixed>,
     *     totals: array<string, int>,
     *     benefits: array<string, mixed>
     * }
     */
    public function execute(QuoteOrderDto $input): array
    {
        if ($input->lines === []) {
            throw new \InvalidArgumentException('Корзина пуста.');
        }

        $deliveryMethod = $input->deliveryMethod === 'pickup' ? 'pickup' : 'courier';
        $orderChannel = $deliveryMethod;

        $productIds = [];
        foreach ($input->lines as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            if ($productId < 1 || $quantity < 1) {
                throw new \InvalidArgumentException('Некорректная строка корзины.');
            }
            $productIds[] = $productId;
        }

        foreach ($input->complementProductIds as $complementId) {
            $productIds[] = (int) $complementId;
        }

        $products = $this->indexProducts(
            $this->catalogItems->findActiveProductsByIds(array_values(array_unique($productIds))),
        );
        $meta = $this->catalogItems->findPromotionMetaByProductIds(array_keys($products));

        $cartLines = [];
        $itemsTotalRubles = 0;
        $rollCount = 0;

        foreach ($input->lines as $line) {
            $productId = (int) $line['product_id'];
            $quantity = (int) $line['quantity'];
            $product = $products[$productId] ?? null;
            if (! $product instanceof Product) {
                throw new \InvalidArgumentException(sprintf('Товар #%d недоступен.', $productId));
            }

            $unit = $product->price()->amountRubles();
            $lineTotal = $unit * $quantity;
            $itemsTotalRubles += $lineTotal;

            if (($meta[$productId]['counts_as_roll'] ?? false) === true) {
                $rollCount += $quantity;
            }

            $cartLines[] = $this->linePayload(
                product: $product,
                quantity: $quantity,
                unitPriceRubles: $unit,
                kind: 'user',
            );
        }

        $policy = $this->promotionPolicies->find();
        $itemsTotalKopecks = $itemsTotalRubles * 100;

        $giftRule = $policy?->giftRuleForChannel($orderChannel);
        $giftThresholdKopecks = is_array($giftRule)
            ? (int) ($giftRule['min_order_amount_kopecks'] ?? 0)
            : 0;
        $giftActive = is_array($giftRule) && ($giftRule['is_active'] ?? false);
        $giftEligible = $giftActive && $itemsTotalKopecks > $giftThresholdKopecks;

        $giftCandidates = [];
        if ($giftEligible) {
            foreach ($this->catalogItems->findActiveSystemProducts() as $systemProduct) {
                $giftCandidates[] = $this->giftCandidatePayload($systemProduct);
            }
        }

        $selectedGiftProductId = null;
        if ($giftEligible && $input->giftProductId !== null) {
            $gift = $this->resolveGiftProduct($input->giftProductId);
            $selectedGiftProductId = $gift->id();

            $cartLines[] = $this->linePayload(
                product: $gift,
                quantity: 1,
                unitPriceRubles: 0,
                kind: 'gift',
            );
        }

        $complementRule = $policy?->complementSetBenefit();
        $rollsPerSet = is_array($complementRule)
            ? (int) ($complementRule['rolls_per_set'] ?? 0)
            : 0;
        $complementActive = is_array($complementRule)
            && ($complementRule['is_active'] ?? false)
            && $rollsPerSet > 0;
        $entitledSets = $complementActive ? intdiv($rollCount, $rollsPerSet) : 0;
        $remainingRollCount = 0;
        if ($complementActive) {
            $remainder = $rollCount % $rollsPerSet;
            $remainingRollCount = $remainder === 0 && $rollCount > 0
                ? 0
                : $rollsPerSet - $remainder;
        }

        $selectedComplements = array_slice(
            array_values(array_unique(array_map('intval', $input->complementProductIds))),
            0,
            max(0, $entitledSets),
        );

        foreach ($selectedComplements as $complementId) {
            $complement = $products[$complementId] ?? null;
            if (! $complement instanceof Product) {
                throw new \InvalidArgumentException(sprintf('Комплект #%d недоступен.', $complementId));
            }
            if (($meta[$complementId]['complement_set'] ?? false) !== true) {
                throw new \InvalidArgumentException(sprintf('Товар #%d не является комплектом дополнений.', $complementId));
            }

            $cartLines[] = $this->linePayload(
                product: $complement,
                quantity: 1,
                unitPriceRubles: 0,
                kind: 'complement',
            );
        }

        $inZone = $this->deliveryPricing->resolveInZone($input->latitude, $input->longitude);
        $deliveryFeeKopecks = $this->deliveryPricing->resolveDeliveryFeeKopecks(
            promotionPolicy: $policy,
            deliveryMethod: $deliveryMethod,
            currentKopecks: $itemsTotalKopecks,
            inZone: $inZone,
        );
        $deliveryFeeRubles = intdiv($deliveryFeeKopecks, 100);

        $client = $input->client;
        if (($client['kind'] ?? null) !== 'registered') {
            $client['kind'] = 'guest';
        }

        $delivery = [
            'method' => $deliveryMethod,
            'address' => $deliveryMethod === 'courier' ? $input->address : null,
            'comment' => $input->deliveryComment,
            'scheduled_at' => $input->scheduledAt,
            'delivery_fee_rubles' => $deliveryFeeRubles,
            'in_zone' => $inZone,
        ];

        $payment = [
            'method' => $input->paymentMethod,
            'change_from_rubles' => $input->changeFromRubles,
        ];

        $freeThreshold = $this->deliveryPricing->resolveFreeDeliveryThresholdKopecks();
        $deliveryBenefit = $policy?->deliveryBenefit();
        $deliveryActive = is_array($deliveryBenefit) && ($deliveryBenefit['is_active'] ?? false);
        $deliveryReached = $freeThreshold !== null && $itemsTotalKopecks >= $freeThreshold;
        $remainingToFree = $freeThreshold !== null
            ? max(0, $freeThreshold - $itemsTotalKopecks)
            : 0;

        $giftRemaining = max(0, ($giftThresholdKopecks + 1) - $itemsTotalKopecks);
        $giftPhase = ! $giftEligible
            ? 'locked'
            : ($selectedGiftProductId !== null ? 'selected' : 'choose');

        return [
            'cart' => [
                'lines' => $cartLines,
                'promo_state' => [
                    'gift_promotion' => [
                        'eligible' => $giftEligible,
                        'phase' => $giftPhase,
                        'selected_product_id' => $selectedGiftProductId,
                        'candidate_items' => $giftCandidates,
                    ],
                ],
            ],
            'client' => $client,
            'delivery' => $delivery,
            'payment' => $payment,
            'totals' => [
                'items_rubles' => $itemsTotalRubles,
                'delivery_fee_rubles' => $deliveryFeeRubles,
                'grand_total_rubles' => $itemsTotalRubles + $deliveryFeeRubles,
            ],
            'benefits' => [
                'gift_eligible' => $giftEligible,
                'gift_selected' => $selectedGiftProductId !== null,
                'gift_threshold_kopecks' => $giftThresholdKopecks,
                'gift_current_kopecks' => $itemsTotalKopecks,
                'gift_remaining_kopecks' => $giftRemaining,
                'gift_active' => $giftActive,
                'gift_candidates' => $giftCandidates,
                'complement_entitled' => $entitledSets,
                'complement_active' => $complementActive,
                'rolls_per_set' => $rollsPerSet,
                'roll_count' => $rollCount,
                'remaining_roll_count' => $remainingRollCount,
                'free_delivery_threshold_kopecks' => $freeThreshold,
                'delivery_active' => $deliveryActive,
                'delivery_reached' => $deliveryReached,
                'remaining_to_free_kopecks' => $remainingToFree,
            ],
        ];
    }

    private function resolveGiftProduct(int $giftProductId): Product
    {
        $gift = $this->catalogItems->findProductById($giftProductId);
        if (
            ! $gift instanceof Product
            || ! $gift->isActive()
            || ! $gift->isSystem()
        ) {
            throw new \InvalidArgumentException('Подарок недоступен.');
        }

        return $gift;
    }

    /**
     * @return array<string, mixed>
     */
    private function giftCandidatePayload(Product $product): array
    {
        $imageUrl = null;
        foreach ($product->images() as $image) {
            $path = $image->path();
            if ($path === '') {
                continue;
            }
            $imageUrl = Storage::disk('public')->url($path);
            break;
        }

        return [
            'id' => $product->id(),
            'name' => $product->name(),
            'price_rub' => 0,
            'image_url' => $imageUrl,
            'composition' => $product->ingredients(),
        ];
    }

    /**
     * @param  list<Product>  $products
     * @return array<int, Product>
     */
    private function indexProducts(array $products): array
    {
        $indexed = [];
        foreach ($products as $product) {
            $indexed[$product->id()] = $product;
        }

        return $indexed;
    }

    /**
     * @return array<string, mixed>
     */
    private function linePayload(
        Product $product,
        int $quantity,
        int $unitPriceRubles,
        string $kind,
    ): array {
        $line = [
            'product_id' => $product->id(),
            'product_name' => $product->name(),
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'payload' => ['kind' => $kind],
        ];

        $sku = $product->sku();
        if (is_string($sku) && $sku !== '') {
            $line['sku'] = $sku;
        }

        return $line;
    }
}
