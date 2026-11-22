<?php

namespace App\Application\Order\Query;

use App\Application\Order\Checkout\CheckoutQuote;
use App\Application\Order\Checkout\FormCheckoutPromotions;
use App\Application\Order\DTO\QuoteOrderDto;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Policy\PromotionPolicy;
use App\Domain\Core\Order\Port\OrderBranchLookupPort;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\Port\OrderClientLookupPort;
use App\Domain\Core\Order\Port\OrderDeliveryTariffPort;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Geo\AddressGeocoder;
use App\Shared\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * Quote считает корзину тем же OrderFormation, что и создание заказа.
 */
final class QuoteOrderUseCase implements CheckoutQuote
{
    public function __construct(
        private readonly OrderCatalogSnapshotPort $catalog,
        private readonly OrderClientLookupPort $clients,
        private readonly OrderDeliveryTariffPort $deliveryTariffs,
        private readonly OrderBranchLookupPort $branches,
        private readonly OrderBranchMenuSnapshotPort $menu,
        private readonly FormCheckoutPromotions $promotions,
        private readonly AddressGeocoder $geocoder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(QuoteOrderDto $dto): array
    {
        if ($dto->lines === []) {
            throw new \InvalidArgumentException('Корзина пуста.');
        }

        $deliveryMethod = $this->normalizeDeliveryMethod($dto->deliveryMethod);
        $paymentMethod = $this->normalizePaymentMethod($dto->paymentMethod);

        $branch = $this->branches->requireActiveBranch($dto->branchId);
        [$cartLines] = $this->buildUserLines($branch['id'], $dto->lines);

        $deliveryConfig = $this->deliveryTariffs->findConfigByBranch($branch['id']);
        if ($deliveryConfig === null) {
            throw new \InvalidArgumentException('Конфигурация доставки филиала не найдена.');
        }

        [$latitude, $longitude] = $this->resolveCoordinates($deliveryMethod, $dto, $branch['city']);
        $zone = $deliveryMethod === DeliveryMethod::Courier->value
            ? $this->deliveryTariffs->matchZoneByCoordinates($branch['id'], $latitude, $longitude)
            : null;

        [$deliveryAvailable, $inZone] = $this->resolveDeliveryAvailability(
            $deliveryMethod,
            $latitude,
            $longitude,
            $zone !== null,
        );

        $scheduledAt = $this->parseScheduledAt($dto->scheduledAt);
        $at = $scheduledAt ?? new DateTimeImmutable;

        if ($deliveryMethod === DeliveryMethod::Courier->value) {
            $deliveryInfo = OrderDeliveryInfo::courier(
                address: $this->deliveryAddress($dto),
                zoneId: $zone !== null ? $zone['id'] : 'outside',
                feeKopecks: $zone !== null ? $zone['fee_kopecks'] : 0,
            );
            $zoneIsRemote = $zone === null || $zone['is_remote'];
        } else {
            $deliveryInfo = OrderDeliveryInfo::pickup();
            $zoneIsRemote = false;
        }

        $formed = $this->promotions->apply(
            $branch['id'],
            $this->toBaseLines($cartLines),
            $deliveryInfo,
            $at,
            $zoneIsRemote,
            $dto->giftProductId,
        );
        $cartLines = $this->presentLines($formed['lines'], $cartLines);
        $itemsTotalKopecks = 0;
        foreach ($formed['lines'] as $formedLine) {
            if ($formedLine->isBillable()) {
                $itemsTotalKopecks += $formedLine->lineTotalKopecks();
            }
        }
        $deliveryFeeKopecks = $formed['delivery']->feeKopecks();
        $deliveryFeeRubles = intdiv($deliveryFeeKopecks, 100);
        $benefits = $this->benefits(
            $formed['policy'],
            $formed['facts'],
            $formed['lines'],
            $deliveryMethod,
            $at,
            $itemsTotalKopecks,
            $deliveryFeeKopecks,
        );

        $itemsTotalRubles = intdiv($itemsTotalKopecks, 100);
        $grandTotalKopecks = $itemsTotalKopecks + $deliveryFeeKopecks;
        $grandTotalRubles = intdiv($grandTotalKopecks, 100);

        $totals = [
            'items_total' => $itemsTotalKopecks,
            'discount' => 0,
            'delivery_fee' => $deliveryFeeKopecks,
            'grand_total' => $grandTotalKopecks,
            'items_rubles' => $itemsTotalRubles,
            'items_total_rubles' => $itemsTotalRubles,
            'delivery_fee_rubles' => $deliveryFeeRubles,
            'grand_total_rubles' => $grandTotalRubles,
        ];

        $branchId = $branch['id'];

        $client = $this->resolveClientSnapshot($dto->client);

        $delivery = [
            'method' => $deliveryMethod,
            'address' => $deliveryMethod === DeliveryMethod::Courier->value ? $dto->address : null,
            'scheduled_at' => $dto->scheduledAt,
            'delivery_fee_rubles' => $deliveryFeeRubles,
            'delivery_fee_kopecks' => $deliveryFeeKopecks,
            'delivery_available' => $deliveryAvailable,
            'in_zone' => $inZone,
            'zone' => $zone !== null ? [
                'id' => $zone['id'],
                'name' => $zone['name'],
                'fee_kopecks' => $zone['fee_kopecks'],
                'is_remote' => $zone['is_remote'],
            ] : null,
            'zone_id' => $zone !== null ? $zone['id'] : null,
            'zone_name' => $zone !== null ? $zone['name'] : null,
            'is_remote' => $zoneIsRemote,
            'fee' => $deliveryFeeKopecks,
            'min_order_kopecks' => $deliveryConfig['min_order_kopecks'],
            'branch_id' => $branchId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        $payment = [
            'method' => $paymentMethod,
            'change_from_rubles' => $dto->changeFrom,
        ];

        $clientRequestId = (string) Str::uuid();

        return [
            'branch_id' => $branchId,
            'cart' => [
                'lines' => $cartLines,
                'items_total' => $itemsTotalKopecks,
                'totals' => $totals,
            ],
            'benefits' => $benefits,
            'client' => $client,
            'delivery' => $delivery,
            'payment' => $payment,
            'totals' => $totals,
            'client_request_id' => $clientRequestId,
            'promo_code' => $dto->promoCode,
        ];
    }

    /**
     * Цены и доступность — только из меню филиала / сетов филиала.
     *
     * @param  list<array{product_id?: int, id?: int, quantity: int, kind?: string}>  $lines
     * @return array{0: list<array<string, mixed>>, 1: int, 2: list<array{product_id: int, quantity: int}>}
     */
    private function buildUserLines(int $branchId, array $lines): array
    {
        $productIds = [];
        $setIds = [];
        foreach ($lines as $line) {
            $id = (int) ($line['id'] ?? $line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            $kind = (string) ($line['kind'] ?? 'product');
            if ($id < 1 || $quantity < 1) {
                throw new \InvalidArgumentException('Некорректная строка корзины.');
            }
            if ($kind === 'set') {
                $setIds[] = $id;
            } else {
                $productIds[] = $id;
            }
        }

        $menuById = $this->menu->findByBranchAndItemIds($branchId, array_values(array_unique($productIds)));
        $products = $this->catalog->findActiveProductsByIds(array_values(array_unique($productIds)));
        $sets = $this->menu->findAvailableSetsByBranchAndIds($branchId, array_values(array_unique($setIds)));

        $cartLines = [];
        $itemsTotalRubles = 0;
        $promoLines = [];

        foreach ($lines as $line) {
            $id = (int) ($line['id'] ?? $line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            $kind = (string) ($line['kind'] ?? 'product');

            if ($kind === 'set') {
                $set = $sets[$id] ?? null;
                if ($set === null || ! ($set['is_available'] ?? false)) {
                    throw new \InvalidArgumentException(sprintf('Сет #%d недоступен в меню филиала.', $id));
                }
                $unitKopecks = (int) $set['price_kopecks'];
                $unit = intdiv($unitKopecks, 100);
                $itemsTotalRubles += $unit * $quantity;
                $cartLine = $this->setLine($set, $quantity, $unit, 'set');
                $cartLine['unit_price_kopecks'] = $unitKopecks;
                $cartLines[] = $cartLine;
                $promoLines[] = ['product_id' => $id, 'quantity' => $quantity];

                continue;
            }

            $menuItem = $menuById[$id] ?? null;
            if ($menuItem === null || ! ($menuItem['is_available'] ?? false)) {
                throw new \InvalidArgumentException(sprintf('Товар #%d недоступен в меню филиала.', $id));
            }
            $product = $products[$id] ?? null;
            if ($product === null) {
                throw new \InvalidArgumentException(sprintf('Товар #%d недоступен.', $id));
            }
            $unitKopecks = (int) $menuItem['price_kopecks'];
            $unit = intdiv($unitKopecks, 100);
            $itemsTotalRubles += $unit * $quantity;
            $cartLine = $this->productLine($product, $quantity, $unit, 'regular');
            $cartLine['unit_price_kopecks'] = $unitKopecks;
            $cartLines[] = $cartLine;
            $promoLines[] = ['product_id' => $id, 'quantity' => $quantity];
        }

        return [$cartLines, $itemsTotalRubles, $promoLines];
    }

    /**
     * @param  list<array<string, mixed>>  $cartLines
     * @return list<OrderLine>
     */
    private function toBaseLines(array $cartLines): array
    {
        $lines = [];
        foreach ($cartLines as $raw) {
            $payload = is_array($raw['payload'] ?? null) ? $raw['payload'] : [];
            $kind = (string) ($payload['kind'] ?? 'regular');
            $type = $kind === 'set' ? OrderLineType::Set : OrderLineType::Regular;
            $kopecks = isset($raw['unit_price_kopecks'])
                ? (int) $raw['unit_price_kopecks']
                : (int) ($raw['unit_price_rubles'] ?? 0) * 100;
            $lines[] = new OrderLine(
                productId: (int) $raw['product_id'],
                name: (string) ($raw['product_name'] ?? ''),
                unitPriceKopecks: $kopecks,
                quantity: max(1, (int) ($raw['quantity'] ?? 1)),
                lineType: $type,
            );
        }

        return $lines;
    }

    private function deliveryAddress(QuoteOrderDto $dto): OrderDeliveryAddress
    {
        $address = $dto->address ?? [];

        return new OrderDeliveryAddress(
            street: (string) ($address['street'] ?? ''),
            house: (string) ($address['house'] ?? ''),
            entrance: isset($address['entrance']) ? (string) $address['entrance'] : null,
            floor: isset($address['floor']) ? (string) $address['floor'] : null,
            apartment: isset($address['apartment']) ? (string) $address['apartment'] : null,
            comment: isset($address['comment']) ? (string) $address['comment'] : null,
        );
    }

    /**
     * @param  list<OrderLine>  $lines
     * @param  list<array<string, mixed>>  $sources
     * @return list<array<string, mixed>>
     */
    private function presentLines(array $lines, array $sources): array
    {
        $byId = [];
        foreach ($sources as $source) {
            $byId[(int) ($source['product_id'] ?? 0)] = $source;
        }

        $presented = [];
        foreach ($lines as $line) {
            $source = $byId[$line->productId()] ?? null;
            $kind = $line->lineType()->value;
            $presented[] = [
                'product_id' => $line->productId(),
                'product_name' => $line->name(),
                'quantity' => $line->quantity(),
                'unit_price_kopecks' => $line->unitPriceKopecks(),
                'unit_price_rubles' => intdiv($line->unitPriceKopecks(), 100),
                'line_total_rubles' => intdiv($line->lineTotalKopecks(), 100),
                'sku' => is_array($source) ? ($source['sku'] ?? null) : null,
                'images' => is_array($source) && is_array($source['images'] ?? null) ? $source['images'] : [],
                'payload' => [
                    'kind' => $kind,
                    'catalog_kind' => $kind === OrderLineType::Set->value ? 'set' : 'product',
                ],
            ];
        }

        return $presented;
    }

    /**
     * @param  list<OrderLine>  $lines
     * @return array<string, mixed>
     */
    private function benefits(
        PromotionPolicy $policy,
        \App\Domain\Core\Order\ValueObject\OrderFormationFacts $facts,
        array $lines,
        string $channel,
        \DateTimeInterface $at,
        int $itemsTotalKopecks,
        int $deliveryFeeKopecks,
    ): array {
        $giftRule = $policy->giftRuleForChannel($channel) ?? [];
        $weekdayOk = $giftRule !== [] && $policy->isGiftRuleAllowedOnDate(
            $giftRule,
            $at,
            (string) config('app.timezone', 'UTC'),
        );
        $giftThreshold = (int) ($giftRule['min_order_amount_kopecks'] ?? 0);
        $thresholdMet = $itemsTotalKopecks >= $giftThreshold;
        $giftCandidatesRaw = $facts->giftCandidates();
        $giftRuleOk = $weekdayOk && $thresholdMet && $giftCandidatesRaw !== [];

        $giftCandidates = [];
        if ($giftRuleOk) {
            foreach ($giftCandidatesRaw as $candidate) {
                $productId = (int) ($candidate['product_id'] ?? 0);
                if ($productId < 1) {
                    continue;
                }
                $giftCandidates[] = [
                    'id' => $productId,
                    'product_id' => $productId,
                    'name' => (string) ($candidate['name'] ?? ''),
                ];
            }
        }

        $giftLine = null;
        $complementQty = 0;
        $rollCount = 0;
        foreach ($lines as $line) {
            if ($line->lineType() === OrderLineType::Gift && $giftLine === null) {
                $giftLine = $line;
            }
            if ($line->lineType() === OrderLineType::Complement) {
                $complementQty = max($complementQty, $line->quantity());
            }
            if ($facts->isRoll($line->productId()) && $line->isBillable()) {
                $rollCount += $line->quantity();
            }
        }

        $complement = $policy->complementSetBenefit();
        $deliveryBenefit = $policy->deliveryBenefit();
        $threshold = (int) ($deliveryBenefit['free_delivery_threshold_kopecks'] ?? 0);
        $rollsPerSet = max(1, (int) ($complement['rolls_per_set'] ?? 1));
        $maxSets = (int) ($complement['max_sets'] ?? 0);
        $entitled = (int) ($complement['is_active'] ?? false)
            ? min(intdiv($rollCount, $rollsPerSet), $maxSets)
            : 0;

        return [
            'gift_active' => (bool) ($giftRule['is_active'] ?? false),
            'gift_weekday_ok' => $weekdayOk,
            'gift_allowed_weekdays' => $giftRule['allowed_weekdays'] ?? [],
            'gift_eligible' => $giftRuleOk,
            'gift_selected' => $giftLine !== null,
            'gift_threshold_kopecks' => $giftThreshold,
            'gift_current_kopecks' => $itemsTotalKopecks,
            'gift_remaining_kopecks' => max(0, $giftThreshold - $itemsTotalKopecks),
            'gift_candidates' => $giftCandidates,
            'rolls_per_set' => $rollsPerSet,
            'complement_max_sets' => $maxSets,
            'roll_count' => $rollCount,
            'complement_entitled' => $complementQty > 0 ? $complementQty : $entitled,
            'remaining_roll_count' => max(0, ($entitled + 1) * $rollsPerSet - $rollCount),
            'complement_active' => (bool) ($complement['is_active'] ?? false),
            'delivery_active' => (bool) ($deliveryBenefit['is_active'] ?? false),
            'delivery_reached' => $channel === DeliveryMethod::Courier->value
                && (bool) ($deliveryBenefit['is_active'] ?? false)
                && $itemsTotalKopecks >= $threshold
                && $deliveryFeeKopecks === 0,
            'free_delivery_threshold_kopecks' => $threshold,
            'remaining_to_free_kopecks' => max(0, $threshold - $itemsTotalKopecks),
        ];
    }

    /**
     * @param  array{kind: string, id?: int|null, name?: string|null, phone?: string|null, email?: string|null}  $client
     * @return array<string, mixed>
     */
    private function resolveClientSnapshot(array $client): array
    {
        $kind = (string) ($client['kind'] ?? 'guest');

        if ($kind === 'client') {
            $clientId = (int) ($client['id'] ?? 0);
            if ($clientId < 1) {
                throw new \InvalidArgumentException('Для клиента нужен id.');
            }

            $found = $this->clients->findById($clientId);
            if ($found === null) {
                throw new \InvalidArgumentException('Клиент не найден.');
            }

            return [
                'kind' => 'client',
                'id' => $found['id'],
                'client_id' => $found['id'],
                'name' => $found['name'],
                'phone' => $found['phone'],
                'email' => $found['email'],
            ];
        }

        $name = trim((string) ($client['name'] ?? ''));
        $phoneRaw = trim((string) ($client['phone'] ?? ''));
        if ($name === '' || $phoneRaw === '') {
            throw new \InvalidArgumentException('Для гостя нужны name и phone.');
        }

        $snapshot = [
            'kind' => 'guest',
            'id' => null,
            'client_id' => null,
            'name' => $name,
            'phone' => PhoneNumber::formatFromRaw($phoneRaw),
        ];

        $email = trim((string) ($client['email'] ?? ''));
        if ($email !== '') {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Некорректный email.');
            }
            $snapshot['email'] = $email;
        }

        return $snapshot;
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveCoordinates(
        string $deliveryMethod,
        QuoteOrderDto $dto,
        ?string $branchCity,
    ): array {
        if ($deliveryMethod !== DeliveryMethod::Courier->value) {
            return [null, null];
        }

        $address = $dto->address ?? [];
        $lat = isset($address['lat']) ? (float) $address['lat'] : null;
        $lng = isset($address['lng']) ? (float) $address['lng'] : null;
        if ($lat !== null && $lng !== null) {
            return [$lat, $lng];
        }

        $street = trim((string) ($address['street'] ?? ''));
        $house = trim((string) ($address['house'] ?? ''));
        if ($street === '' || $house === '') {
            throw new \InvalidArgumentException('Для курьера нужны street и house.');
        }

        $coords = $this->geocoder->geocode($street, $house, $branchCity);

        return [$coords['latitude'] ?? null, $coords['longitude'] ?? null];
    }

    /**
     * @return array{0: ?bool, 1: ?bool}
     */
    private function resolveDeliveryAvailability(
        string $deliveryMethod,
        ?float $latitude,
        ?float $longitude,
        bool $zoneMatched,
    ): array {
        if ($deliveryMethod !== DeliveryMethod::Courier->value) {
            return [true, null];
        }

        if ($latitude === null || $longitude === null) {
            return [null, null];
        }

        return $zoneMatched ? [true, true] : [false, false];
    }

    private function normalizeDeliveryMethod(string $method): string
    {
        $normalized = DeliveryMethod::tryFrom(trim($method));
        if ($normalized === null) {
            throw new \InvalidArgumentException('Некорректный способ доставки.');
        }

        return $normalized->value;
    }

    private function normalizePaymentMethod(string $method): string
    {
        $allowed = ['cash', 'card_courier', 'card_online'];
        $method = trim($method);
        if (! in_array($method, $allowed, true)) {
            throw new \InvalidArgumentException('Некорректный способ оплаты.');
        }

        return $method;
    }

    private function parseScheduledAt(?string $scheduledAt): ?DateTimeImmutable
    {
        if ($scheduledAt === null || trim($scheduledAt) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable(trim($scheduledAt));
        } catch (\Exception) {
            throw new \InvalidArgumentException('Некорректный scheduled_at.');
        }
    }

    /**
     * @param  array{id: int, name: string, sku: string|null, images: list<string>}  $product
     * @return array<string, mixed>
     */
    private function productLine(array $product, int $quantity, int $unitPriceRubles, string $kind): array
    {
        return [
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'sku' => $product['sku'],
            'images' => $product['images'],
            'payload' => [
                'kind' => $kind,
                'catalog_kind' => 'product',
            ],
        ];
    }

    /**
     * @param  array{id: int, name: string, price_kopecks: int, composition: list<array{product_id: int, quantity: int}>}  $set
     * @return array<string, mixed>
     */
    private function setLine(array $set, int $quantity, int $unitPriceRubles, string $kind): array
    {
        return [
            'product_id' => $set['id'],
            'product_name' => $set['name'],
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'sku' => null,
            'images' => [],
            'payload' => [
                'kind' => $kind,
                'catalog_kind' => 'set',
                'composition' => $set['composition'],
            ],
        ];
    }
}
