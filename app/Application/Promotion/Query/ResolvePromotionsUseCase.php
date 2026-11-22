<?php

namespace App\Application\Promotion\Query;

use App\Application\Order\Checkout\FormCheckoutPromotions;
use App\Application\Promotion\DTO\ResolvePromotionsDto;
use App\Application\Promotion\Presenter\AppliedPromotionsPresenter;
use App\Domain\Core\Delivery\Tariff\Repository\DeliveryConfigurationRepository;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Domain\Supporting\Catalog\Repository\CatalogItemRepository;
use App\Domain\Supporting\Company\Repository\BranchMenuRepository;
use App\Shared\Enum\DeliveryMethod;
use DateTimeImmutable;

/**
 * Тот же OrderFormation, что quote и create. Отдельного расчёта акций нет.
 * Цены — только из меню филиала.
 */
final class ResolvePromotionsUseCase
{
    public function __construct(
        private readonly CatalogItemRepository $catalog,
        private readonly BranchMenuRepository $menu,
        private readonly DeliveryConfigurationRepository $deliveryConfigs,
        private readonly FormCheckoutPromotions $promotions,
        private readonly AppliedPromotionsPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ResolvePromotionsDto $dto): array
    {
        $baseLines = $this->baseLines($dto->branchId, $dto->lines);
        $channel = $dto->deliveryMethod === DeliveryMethod::Pickup->value
            ? DeliveryMethod::Pickup->value
            : DeliveryMethod::Courier->value;

        if ($channel === DeliveryMethod::Courier->value) {
            [$zoneId, $fee, $remote] = $this->zone($dto->branchId, $dto->deliveryZoneId);
            $delivery = OrderDeliveryInfo::courier(
                address: new OrderDeliveryAddress(street: 'Зона', house: '1'),
                zoneId: $zoneId,
                feeKopecks: $fee,
            );
        } else {
            $delivery = OrderDeliveryInfo::pickup();
            $fee = 0;
            $remote = false;
        }

        $formed = $this->promotions->apply(
            $dto->branchId,
            $baseLines,
            $delivery,
            $dto->scheduledAt ?? new DateTimeImmutable,
            $remote,
        );

        $gift = null;
        $complements = [];
        $rollCount = 0;
        foreach ($formed['lines'] as $line) {
            if ($line->lineType() === OrderLineType::Gift && $gift === null) {
                $gift = [
                    'product_id' => $line->productId(),
                    'name' => $line->name(),
                    'price_kopecks' => 0,
                ];
            }
            if ($line->lineType() === OrderLineType::Complement) {
                $complements[] = [
                    'product_id' => $line->productId(),
                    'name' => $line->name(),
                    'quantity' => $line->quantity(),
                    'price_kopecks' => 0,
                ];
            }
            if ($formed['facts']->isRoll($line->productId()) && $line->isBillable()) {
                $rollCount += $line->quantity();
            }
        }

        $formedFee = $formed['delivery']->feeKopecks();

        return $this->presenter->present([
            'gift' => $gift,
            'complements' => $complements,
            'free_delivery' => $channel === DeliveryMethod::Courier->value && $fee > 0 && $formedFee === 0,
            'delivery_discount_kopecks' => max(0, $fee - $formedFee),
            'roll_count' => $rollCount,
            'complement_entitled' => $complements === [] ? 0 : $complements[0]['quantity'],
        ]);
    }

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lines
     * @return list<OrderLine>
     */
    private function baseLines(int $branchId, array $lines): array
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
        $menuById = $this->menu->findByBranchAndItemIds($branchId, $uniqueIds);

        $products = [];
        foreach ($this->catalog->findActiveProductsByIds($uniqueIds) as $product) {
            $products[$product->id()] = $product;
        }
        $sets = [];
        foreach ($this->catalog->findActiveSetsByIds($uniqueIds) as $set) {
            $sets[$set->id()] = $set;
        }

        $base = [];
        foreach ($lines as $line) {
            $productId = (int) $line['product_id'];
            $quantity = (int) $line['quantity'];
            $menuItem = $menuById[$productId] ?? null;
            if ($menuItem === null || ! $menuItem->isAvailable()) {
                throw new \InvalidArgumentException(sprintf('Товар #%d недоступен в меню филиала.', $productId));
            }

            if (isset($products[$productId])) {
                $item = $products[$productId];
                $type = OrderLineType::Regular;
            } elseif (isset($sets[$productId])) {
                $item = $sets[$productId];
                $type = OrderLineType::Set;
            } else {
                throw new \InvalidArgumentException(sprintf('Товар #%d недоступен.', $productId));
            }

            $base[] = new OrderLine(
                productId: $productId,
                name: $item->name(),
                unitPriceKopecks: $menuItem->priceKopecks(),
                quantity: $quantity,
                lineType: $type,
            );
        }

        return $base;
    }

    /**
     * @return array{0: string, 1: int, 2: bool}
     */
    private function zone(int $branchId, ?string $zoneId): array
    {
        $config = $this->deliveryConfigs->findByBranch($branchId);
        if ($config === null || $zoneId === null || $zoneId === '') {
            return ['outside', 0, true];
        }

        foreach ($config->zones() as $zone) {
            if ($zone->id() === $zoneId) {
                return [$zone->id(), $zone->deliveryFeeKopecks(), $zone->isRemote()];
            }
        }

        return ['outside', 0, true];
    }
}
