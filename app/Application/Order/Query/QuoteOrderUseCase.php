<?php

namespace App\Application\Order\Query;

use App\Application\Order\DTO\QuoteOrderDto;
use App\Domain\Order\Port\OrderCatalogPort;
use App\Domain\Order\Port\OrderClientLookupPort;
use App\Domain\Order\Port\PromotionDeliveryPricingPort;
use App\Domain\Order\Repository\PromotionPolicyRepository;
use App\Shared\Geo\AddressGeocoder;
use Illuminate\Support\Facades\Storage;

/**
 * Статичный quote: цены каталога + правила акций → snapshot для place.
 */
final class QuoteOrderUseCase
{
    public function __construct(
        private readonly OrderCatalogPort $catalog,
        private readonly PromotionPolicyRepository $promotionPolicies,
        private readonly PromotionDeliveryPricingPort $deliveryPricing,
        private readonly AddressGeocoder $addressGeocoder,
        private readonly OrderClientLookupPort $clientLookup,
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

        $lineCatalogIds = [];
        foreach ($input->lines as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            if ($productId < 1 || $quantity < 1) {
                throw new \InvalidArgumentException('Некорректная строка корзины.');
            }
            $lineCatalogIds[] = $productId;
        }

        $productLookupIds = $lineCatalogIds;
        foreach ($input->complementProductIds as $complementId) {
            $productLookupIds[] = (int) $complementId;
        }

        $products = $this->indexById(
            $this->catalog->findActiveProductsByIds(array_values(array_unique($productLookupIds))),
        );
        $sets = $this->indexById(
            $this->catalog->findActiveSetsByIds(array_values(array_unique($lineCatalogIds))),
        );

        $metaProductIds = array_keys($products);
        foreach ($sets as $set) {
            foreach ($set['lines'] as $setLine) {
                $metaProductIds[] = $setLine['product_id'];
            }
        }
        $meta = $this->catalog->findPromotionMetaByProductIds(
            array_values(array_unique($metaProductIds)),
        );

        $cartLines = [];
        $itemsTotalRubles = 0;
        $rollCount = 0;

        foreach ($input->lines as $line) {
            $catalogId = (int) $line['product_id'];
            $quantity = (int) $line['quantity'];
            $product = $products[$catalogId] ?? null;
            $set = $sets[$catalogId] ?? null;

            if ($product !== null) {
                $unit = (int) $product['price_rubles'];
                $itemsTotalRubles += $unit * $quantity;
                $rollCount += $this->rollCountForProduct($catalogId, $quantity, $meta);
                $cartLines[] = $this->linePayload(
                    product: $product,
                    quantity: $quantity,
                    unitPriceRubles: $unit,
                    kind: 'user',
                );

                continue;
            }

            if ($set !== null) {
                $unit = (int) $set['price_rubles'];
                $itemsTotalRubles += $unit * $quantity;
                $rollCount += $this->rollCountForSet($set, $quantity, $meta);
                $cartLines[] = $this->setLinePayload(
                    set: $set,
                    quantity: $quantity,
                    unitPriceRubles: $unit,
                    kind: 'user',
                );

                continue;
            }

            throw new \InvalidArgumentException(sprintf('Товар #%d недоступен.', $catalogId));
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
            foreach ($this->catalog->findActiveSystemProducts() as $systemProduct) {
                $giftCandidates[] = $this->giftCandidatePayload($systemProduct);
            }
        }

        $selectedGiftProductId = null;
        if ($giftEligible && $input->giftProductId !== null) {
            $gift = $this->resolveGiftProduct($input->giftProductId);
            $selectedGiftProductId = (int) $gift['id'];

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
        $entitledSets = $complementActive && $rollCount > 0
            ? (int) ceil($rollCount / $rollsPerSet)
            : 0;
        $remainingRollCount = 0;
        if ($complementActive) {
            $remainingRollCount = $this->remainingRollCountForComplement(
                rollCount: $rollCount,
                entitledSets: $entitledSets,
                rollsPerSet: $rollsPerSet,
            );
        }

        $complementProducts = $entitledSets > 0
            ? $this->indexById($this->catalog->findActiveComplementSetProducts())
            : [];

        foreach ($complementProducts as $complementId => $complementProduct) {
            $products[$complementId] = $complementProduct;
            $meta[$complementId] = [
                'counts_as_roll' => false,
                'complement_set' => true,
            ];
        }

        foreach (
            $this->resolveComplementProducts(
                entitledSets: $entitledSets,
                requestedIds: $input->complementProductIds,
                availableById: $complementProducts,
            ) as $complement
        ) {
            $complementId = (int) $complement['id'];
            $selectedQty = $this->resolveComplementQuantity(
                productId: $complementId,
                entitledSets: $entitledSets,
                selectionsById: $input->complementSelections,
            );

            if ($selectedQty <= 0) {
                continue;
            }

            $cartLines[] = $this->linePayload(
                product: $complement,
                quantity: $selectedQty,
                unitPriceRubles: 0,
                kind: 'complement',
            );
        }

        [$latitude, $longitude] = $this->resolveCoordinates($deliveryMethod, $input);
        $inZone = $this->deliveryPricing->resolveInZone($latitude, $longitude);
        $deliveryFeeKopecks = $this->deliveryPricing->resolveDeliveryFeeKopecks(
            promotionPolicy: $policy,
            deliveryMethod: $deliveryMethod,
            currentKopecks: $itemsTotalKopecks,
            inZone: $inZone,
        );
        $deliveryFeeRubles = intdiv($deliveryFeeKopecks, 100);

        $client = $this->resolveClientSnapshot($input->client);

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

    /**
     * @return array<string, mixed>
     */
    private function resolveGiftProduct(int $giftProductId): array
    {
        $gift = $this->catalog->findProductById($giftProductId);
        if (
            $gift === null
            || ($gift['is_active'] ?? false) !== true
            || ($gift['is_system'] ?? false) !== true
        ) {
            throw new \InvalidArgumentException('Подарок недоступен.');
        }

        return $gift;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function giftCandidatePayload(array $product): array
    {
        $imageUrl = null;
        foreach ($product['image_paths'] as $path) {
            if ($path === '') {
                continue;
            }
            $imageUrl = Storage::disk('public')->url($path);
            break;
        }

        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'price_rub' => 0,
            'image_url' => $imageUrl,
            'composition' => $product['ingredients'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function indexById(array $items): array
    {
        $indexed = [];
        foreach ($items as $item) {
            $indexed[(int) $item['id']] = $item;
        }

        return $indexed;
    }

    /**
     * @param  array<int, array{counts_as_roll: bool, complement_set: bool}>  $meta
     */
    private function rollCountForProduct(int $productId, int $quantity, array $meta): int
    {
        if (($meta[$productId]['counts_as_roll'] ?? false) !== true) {
            return 0;
        }

        return $quantity;
    }

    /**
     * Роллы внутри набора: quantity компонента × qty набора.
     *
     * @param  array<string, mixed>  $set
     * @param  array<int, array{counts_as_roll: bool, complement_set: bool}>  $meta
     */
    private function rollCountForSet(array $set, int $setQuantity, array $meta): int
    {
        $rolls = 0;

        foreach ($set['lines'] as $setLine) {
            $componentId = (int) $setLine['product_id'];
            if (($meta[$componentId]['counts_as_roll'] ?? false) !== true) {
                continue;
            }

            $rolls += (int) $setLine['quantity'] * $setQuantity;
        }

        return $rolls;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function linePayload(
        array $product,
        int $quantity,
        int $unitPriceRubles,
        string $kind,
    ): array {
        $line = [
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'payload' => [
                'kind' => $kind,
                'catalog_kind' => 'product',
            ],
        ];

        $sku = $product['sku'] ?? null;
        if (is_string($sku) && $sku !== '') {
            $line['sku'] = $sku;
        }

        return $line;
    }

    /**
     * @param  array<string, mixed>  $set
     * @return array<string, mixed>
     */
    private function setLinePayload(
        array $set,
        int $quantity,
        int $unitPriceRubles,
        string $kind,
    ): array {
        $composition = [];
        foreach ($set['lines'] as $setLine) {
            $composition[] = [
                'product_id' => (int) $setLine['product_id'],
                'quantity' => (int) $setLine['quantity'],
            ];
        }

        $line = [
            'product_id' => $set['id'],
            'product_name' => $set['name'],
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'payload' => [
                'kind' => $kind,
                'catalog_kind' => 'set',
                'composition' => $composition,
            ],
        ];

        $sku = $set['sku'] ?? null;
        if (is_string($sku) && $sku !== '') {
            $line['sku'] = $sku;
        }

        return $line;
    }

    /**
     * Snapshot клиента для quote/place: registered берём из CRM по client_id.
     *
     * @param  array<string, mixed>  $client
     * @return array<string, mixed>
     */
    private function resolveClientSnapshot(array $client): array
    {
        $kind = $client['kind'] ?? null;
        $clientId = isset($client['client_id']) ? (int) $client['client_id'] : 0;

        if ($kind === 'registered' && $clientId > 0) {
            $found = $this->clientLookup->findSnapshotById($clientId);
            if ($found !== null) {
                $snapshot = [
                    'kind' => 'registered',
                    'client_id' => $found['id'],
                    'name' => $found['name'],
                    'phone' => $found['phone'],
                ];

                $email = $found['email'];
                if (is_string($email) && $email !== '') {
                    $snapshot['email'] = $email;
                }

                return $snapshot;
            }
        }

        if ($kind !== 'registered') {
            $client['kind'] = 'guest';
        }

        return $client;
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveCoordinates(string $deliveryMethod, QuoteOrderDto $input): array
    {
        $latitude = $input->latitude;
        $longitude = $input->longitude;

        if (
            $deliveryMethod !== 'courier'
            || ($latitude !== null && $longitude !== null)
        ) {
            return [$latitude, $longitude];
        }

        $address = is_array($input->address) ? $input->address : [];
        $street = trim((string) ($address['street'] ?? ''));
        $house = trim((string) ($address['house'] ?? ''));
        $city = isset($address['city']) ? trim((string) $address['city']) : null;
        if ($city === '') {
            $city = null;
        }
        if ($city === null) {
            $city = $this->deliveryPricing->resolveKitchenCity();
        }

        $coords = $this->addressGeocoder->geocode($street, $house, $city);
        if ($coords === null) {
            return [null, null];
        }

        return [$coords['latitude'], $coords['longitude']];
    }

    /**
     * Роллов до следующего комплекта при округлении вверх (ceil).
     */
    private function remainingRollCountForComplement(
        int $rollCount,
        int $entitledSets,
        int $rollsPerSet,
    ): int {
        if ($rollCount <= 0) {
            return $rollsPerSet > 0 ? 1 : 0;
        }

        $nextThreshold = ($entitledSets * $rollsPerSet) + 1;

        return max(0, $nextThreshold - $rollCount);
    }

    /**
     * @param  array<int, int>  $selectionsById
     */
    private function resolveComplementQuantity(
        int $productId,
        int $entitledSets,
        array $selectionsById,
    ): int {
        if ($entitledSets <= 0) {
            return 0;
        }

        if (array_key_exists($productId, $selectionsById)) {
            return min(max(0, $selectionsById[$productId]), $entitledSets);
        }

        return $entitledSets;
    }

    /**
     * Все активные наборы дополнений (или явный whitelist с FE), каждый × selected qty.
     *
     * @param  list<int>  $requestedIds
     * @param  array<int, array<string, mixed>>  $availableById
     * @return list<array<string, mixed>>
     */
    private function resolveComplementProducts(
        int $entitledSets,
        array $requestedIds,
        array $availableById,
    ): array {
        if ($entitledSets <= 0 || $availableById === []) {
            return [];
        }

        $requested = [];
        foreach ($requestedIds as $rawId) {
            $id = (int) $rawId;
            if ($id >= 1 && isset($availableById[$id])) {
                $requested[$id] = $availableById[$id];
            }
        }

        if ($requested !== []) {
            return array_values($requested);
        }

        return array_values($availableById);
    }
}
