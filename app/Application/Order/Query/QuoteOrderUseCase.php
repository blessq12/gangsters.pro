<?php

namespace App\Application\Order\Query;

use App\Application\Order\DTO\QuoteOrderDto;
use App\Application\Promotion\DTO\ResolvePromotionsDto;
use App\Application\Promotion\Query\ResolvePromotionsUseCase;
use App\Domain\Catalog\Entity\Product;
use App\Domain\Catalog\Entity\ProductSet;
use App\Domain\Catalog\Repository\CatalogItemRepository;
use App\Domain\Company\Repository\BranchRepository;
use App\Domain\Company\Repository\CompanyRepository;
use App\Domain\Company\Repository\KitchenRepository;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Delivery\Entity\DeliveryConfiguration;
use App\Domain\Delivery\Repository\DeliveryConfigurationRepository;
use App\Domain\Delivery\ValueObject\DeliveryZone;
use App\Shared\Enum\DeliveryMethod;
use App\Shared\Geo\AddressGeocoder;
use App\Shared\Geo\DeliveryZoneMatcher;
use App\Shared\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Support\Str;

/**
 * Quote: снимки Catalog/Client/Delivery/Company + ResolvePromotions → snapshot для place.
 */
final class QuoteOrderUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $catalog,
        private readonly ClientRepository $clients,
        private readonly DeliveryConfigurationRepository $deliveryConfigs,
        private readonly CompanyRepository $companies,
        private readonly BranchRepository $branches,
        private readonly KitchenRepository $kitchens,
        private readonly ResolvePromotionsUseCase $resolvePromotions,
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

        [$cartLines, $itemsTotalRubles, $promoLines] = $this->buildUserLines($dto->lines);
        $itemsTotalKopecks = $itemsTotalRubles * 100;

        $deliveryConfig = $this->deliveryConfigs->findPublic();
        if ($deliveryConfig === null) {
            throw new \InvalidArgumentException('Конфигурация доставки не найдена.');
        }

        [$latitude, $longitude] = $this->resolveCoordinates($deliveryMethod, $dto, $deliveryConfig);
        $zone = $deliveryMethod === DeliveryMethod::Courier->value
            ? DeliveryZoneMatcher::match($deliveryConfig->zones(), $latitude, $longitude)
            : null;

        [$deliveryAvailable, $inZone] = $this->resolveDeliveryAvailability(
            $deliveryMethod,
            $latitude,
            $longitude,
            $zone !== null,
        );

        $scheduledAt = $this->parseScheduledAt($dto->scheduledAt);

        $applied = $this->resolvePromotions->execute(new ResolvePromotionsDto(
            lines: $promoLines,
            deliveryMethod: $deliveryMethod,
            deliveryZoneId: $zone?->id(),
            currentTotalKopecks: $itemsTotalKopecks,
            scheduledAt: $scheduledAt,
        ));

        $cartLines = $this->applyGiftLine($cartLines, $applied, $dto->giftProductId);
        $cartLines = $this->applyComplementLines($cartLines, $applied, $dto->complementSelections);

        $baseFeeKopecks = $deliveryMethod === DeliveryMethod::Courier->value && $zone !== null
            ? $zone->deliveryFeeKopecks()
            : 0;
        $deliveryDiscountKopecks = (int) ($applied['delivery_discount_kopecks'] ?? 0);
        $freeDelivery = (bool) ($applied['free_delivery'] ?? false);
        $deliveryFeeKopecks = $freeDelivery
            ? 0
            : max(0, $baseFeeKopecks - $deliveryDiscountKopecks);
        $deliveryFeeRubles = intdiv($deliveryFeeKopecks, 100);

        if ($deliveryFeeRubles > 0 && $zone !== null) {
            $deliveryLine = $this->resolveDeliveryFeeLine($zone);
            if ($deliveryLine !== null) {
                $cartLines[] = $deliveryLine;
            }
        }

        $discountKopecks = $deliveryDiscountKopecks;
        $grandTotalKopecks = $itemsTotalKopecks + $deliveryFeeKopecks;
        $grandTotalRubles = intdiv($grandTotalKopecks, 100);

        $totals = [
            'items_total' => $itemsTotalKopecks,
            'discount' => $discountKopecks,
            'delivery_fee' => $deliveryFeeKopecks,
            'grand_total' => $grandTotalKopecks,
            'items_total_rubles' => $itemsTotalRubles,
            'delivery_fee_rubles' => $deliveryFeeRubles,
            'grand_total_rubles' => $grandTotalRubles,
        ];

        $kitchen = $deliveryConfig->kitchenAddress();
        $companySnapshot = $this->resolveCompanySnapshot();

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
                'id' => $zone->id(),
                'name' => $zone->name(),
                'fee_kopecks' => $zone->deliveryFeeKopecks(),
                'is_remote' => $zone->isRemote(),
            ] : null,
            'zone_id' => $zone?->id(),
            'zone_name' => $zone?->name(),
            'is_remote' => $zone?->isRemote(),
            'fee' => $deliveryFeeKopecks,
            'min_order_kopecks' => $deliveryConfig->minOrderAmountKopecks(),
            'kitchen_address' => [
                'city' => $kitchen->city(),
                'street' => $kitchen->street(),
                'house' => $kitchen->house(),
                'comment' => $kitchen->comment(),
                'search_line' => $kitchen->searchLine(),
                'latitude' => $deliveryConfig->kitchenLatitude(),
                'longitude' => $deliveryConfig->kitchenLongitude(),
            ],
            'branch_id' => $companySnapshot['branch_id'],
            'kitchen_id' => $companySnapshot['kitchen_id'],
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        $payment = [
            'method' => $paymentMethod,
            'change_from_rubles' => $dto->changeFrom,
        ];

        $clientRequestId = (string) Str::uuid();

        return [
            'cart' => [
                'lines' => $cartLines,
                'items_total' => $itemsTotalKopecks,
                'discounts' => [
                    'delivery_discount_kopecks' => $deliveryDiscountKopecks,
                ],
                'applied_promotions' => $applied,
                'totals' => $totals,
            ],
            'client' => $client,
            'delivery' => $delivery,
            'payment' => $payment,
            'totals' => $totals,
            'client_request_id' => $clientRequestId,
            'promo_code' => $dto->promoCode,
        ];
    }

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @return array{0: list<array<string, mixed>>, 1: int, 2: list<array{product_id: int, quantity: int}>}
     */
    private function buildUserLines(array $lines): array
    {
        $ids = [];
        foreach ($lines as $line) {
            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            if ($productId < 1 || $quantity < 1) {
                throw new \InvalidArgumentException('Некорректная строка корзины.');
            }
            $ids[] = $productId;
        }

        $uniqueIds = array_values(array_unique($ids));
        $products = $this->indexProducts($this->catalog->findActiveProductsByIds($uniqueIds));
        $sets = $this->indexSets($this->catalog->findActiveSetsByIds($uniqueIds));

        $cartLines = [];
        $itemsTotalRubles = 0;
        $promoLines = [];

        foreach ($lines as $line) {
            $catalogId = (int) $line['product_id'];
            $quantity = (int) $line['quantity'];

            if (isset($products[$catalogId])) {
                $product = $products[$catalogId];
                $unit = $product->regularPrice()?->amountRubles() ?? 0;
                $itemsTotalRubles += $unit * $quantity;
                $cartLines[] = $this->productLine($product, $quantity, $unit, 'user');
                $promoLines[] = ['product_id' => $catalogId, 'quantity' => $quantity];

                continue;
            }

            if (isset($sets[$catalogId])) {
                $set = $sets[$catalogId];
                $unit = $set->regularPrice()?->amountRubles() ?? 0;
                $itemsTotalRubles += $unit * $quantity;
                $cartLines[] = $this->setLine($set, $quantity, $unit, 'user');
                $promoLines[] = ['product_id' => $catalogId, 'quantity' => $quantity];

                continue;
            }

            throw new \InvalidArgumentException(sprintf('Товар #%d недоступен.', $catalogId));
        }

        return [$cartLines, $itemsTotalRubles, $promoLines];
    }

    /**
     * @param  list<array<string, mixed>>  $cartLines
     * @param  array<string, mixed>  $applied
     * @return list<array<string, mixed>>
     */
    private function applyGiftLine(array $cartLines, array $applied, ?int $giftProductId): array
    {
        $gift = is_array($applied['gift'] ?? null) ? $applied['gift'] : null;
        if ($gift === null || $giftProductId === null || $giftProductId < 1) {
            return $cartLines;
        }

        $selectedId = $giftProductId;
        if ((int) ($gift['product_id'] ?? 0) !== $giftProductId) {
            $product = $this->catalog->findProductById($giftProductId);
            if ($product === null || ! $product->isActive()) {
                throw new \InvalidArgumentException('Подарок недоступен.');
            }
            $name = $product->name();
            $sku = $product->sku();
            $images = $this->imagePaths($product);
        } else {
            $name = (string) ($gift['name'] ?? 'Подарок');
            $sku = null;
            $images = [];
            $product = $this->catalog->findProductById($selectedId);
            if ($product !== null) {
                $sku = $product->sku();
                $images = $this->imagePaths($product);
            }
        }

        $cartLines[] = [
            'product_id' => $selectedId,
            'product_name' => $name,
            'quantity' => 1,
            'unit_price_rubles' => 0,
            'line_total_rubles' => 0,
            'sku' => $sku,
            'images' => $images,
            'payload' => [
                'kind' => 'gift',
                'catalog_kind' => 'product',
            ],
        ];

        return $cartLines;
    }

    /**
     * @param  list<array<string, mixed>>  $cartLines
     * @param  array<string, mixed>  $applied
     * @param  array<int, int>  $selections
     * @return list<array<string, mixed>>
     */
    private function applyComplementLines(array $cartLines, array $applied, array $selections): array
    {
        $complements = is_array($applied['complements'] ?? null) ? $applied['complements'] : [];
        $entitled = (int) ($applied['complement_entitled'] ?? 0);
        if ($entitled < 1 || $complements === []) {
            return $cartLines;
        }

        foreach ($complements as $complement) {
            if (! is_array($complement)) {
                continue;
            }
            $productId = (int) ($complement['product_id'] ?? 0);
            if ($productId < 1) {
                continue;
            }

            $qty = array_key_exists($productId, $selections)
                ? min(max(0, (int) $selections[$productId]), $entitled)
                : min((int) ($complement['quantity'] ?? $entitled), $entitled);

            if ($qty < 1) {
                continue;
            }

            $cartLines[] = [
                'product_id' => $productId,
                'product_name' => (string) ($complement['name'] ?? 'Комплект'),
                'quantity' => $qty,
                'unit_price_rubles' => 0,
                'line_total_rubles' => 0,
                'payload' => [
                    'kind' => 'complement',
                    'catalog_kind' => 'product',
                ],
            ];
        }

        return $cartLines;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveDeliveryFeeLine(DeliveryZone $zone): ?array
    {
        $productId = $zone->deliveryProductId();
        if ($productId === null || $productId < 1) {
            return null;
        }

        $product = $this->catalog->findProductById($productId);
        if ($product === null || ! $product->isActive()) {
            return null;
        }

        $unit = $product->regularPrice()?->amountRubles() ?? 0;

        return $this->productLine($product, 1, $unit, 'delivery');
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
                'client_id' => $found->id(),
                'name' => $found->name(),
                'phone' => $found->phone(),
                'email' => $found->email(),
            ];
        }

        $name = trim((string) ($client['name'] ?? ''));
        $phoneRaw = trim((string) ($client['phone'] ?? ''));
        if ($name === '' || $phoneRaw === '') {
            throw new \InvalidArgumentException('Для гостя нужны name и phone.');
        }

        $snapshot = [
            'kind' => 'guest',
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
     * @return array{branch_id: int|null, kitchen_id: int|null}
     */
    private function resolveCompanySnapshot(): array
    {
        $company = $this->companies->findPublic();
        if ($company === null) {
            return ['branch_id' => null, 'kitchen_id' => null];
        }

        $branchId = null;
        foreach ($this->branches->listByCompany($company->id()) as $branch) {
            if ($branch->isActive()) {
                $branchId = $branch->id();
                break;
            }
        }

        $kitchenId = null;
        if ($branchId !== null) {
            foreach ($this->kitchens->listByCompany($company->id()) as $kitchen) {
                if ($kitchen->branchId() === $branchId) {
                    $kitchenId = $kitchen->id();
                    break;
                }
            }
        }

        return [
            'branch_id' => $branchId,
            'kitchen_id' => $kitchenId,
        ];
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveCoordinates(
        string $deliveryMethod,
        QuoteOrderDto $dto,
        DeliveryConfiguration $config,
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

        $city = $config->kitchenAddress()->city();
        $coords = $this->geocoder->geocode($street, $house, $city);

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
     * @param  list<ProductSet>  $sets
     * @return array<int, ProductSet>
     */
    private function indexSets(array $sets): array
    {
        $indexed = [];
        foreach ($sets as $set) {
            $indexed[$set->id()] = $set;
        }

        return $indexed;
    }

    /**
     * @return array<string, mixed>
     */
    private function productLine(Product $product, int $quantity, int $unitPriceRubles, string $kind): array
    {
        $line = [
            'product_id' => $product->id(),
            'product_name' => $product->name(),
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'sku' => $product->sku(),
            'images' => $this->imagePaths($product),
            'payload' => [
                'kind' => $kind,
                'catalog_kind' => 'product',
            ],
        ];

        return $line;
    }

    /**
     * @return array<string, mixed>
     */
    private function setLine(ProductSet $set, int $quantity, int $unitPriceRubles, string $kind): array
    {
        $composition = [];
        foreach ($set->lines() as $setLine) {
            $composition[] = [
                'product_id' => $setLine->productId(),
                'quantity' => $setLine->quantity(),
            ];
        }

        return [
            'product_id' => $set->id(),
            'product_name' => $set->name(),
            'quantity' => $quantity,
            'unit_price_rubles' => $unitPriceRubles,
            'line_total_rubles' => $unitPriceRubles * $quantity,
            'sku' => $set->sku(),
            'images' => $this->imagePathsFromList($set->images()),
            'payload' => [
                'kind' => $kind,
                'catalog_kind' => 'set',
                'composition' => $composition,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private function imagePaths(Product $product): array
    {
        return $this->imagePathsFromList($product->images());
    }

    /**
     * @param  list<\App\Domain\Catalog\ValueObject\ProductImage>  $images
     * @return list<string>
     */
    private function imagePathsFromList(array $images): array
    {
        $paths = [];
        foreach ($images as $image) {
            $paths[] = $image->path();
        }

        return $paths;
    }
}
