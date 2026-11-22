<?php

namespace App\Application\Order\Query;

use App\Application\Order\Checkout\FormCheckoutPromotions;
use App\Application\Promotion\DTO\ResolvePromotionsDto;
use App\Application\Promotion\Presenter\AppliedPromotionsPresenter;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Port\OrderBranchMenuSnapshotPort;
use App\Domain\Core\Order\Port\OrderCatalogSnapshotPort;
use App\Domain\Core\Order\Port\OrderDeliveryTariffPort;
use App\Domain\Core\Order\ValueObject\OrderDeliveryAddress;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use App\Shared\Enum\DeliveryMethod;
use DateTimeImmutable;

/**
 * Тот же OrderFormation, что quote и create. Чужие BC — только через порты Order.
 */
final class ResolvePromotionsUseCase
{
    public function __construct(
        private readonly OrderCatalogSnapshotPort $catalog,
        private readonly OrderBranchMenuSnapshotPort $menu,
        private readonly OrderDeliveryTariffPort $deliveryTariffs,
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
     * @param  list<array{product_id?: int, id?: int, quantity: int, kind?: string}>  $lines
     * @return list<OrderLine>
     */
    private function baseLines(int $branchId, array $lines): array
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

        $base = [];
        foreach ($lines as $line) {
            $id = (int) ($line['id'] ?? $line['product_id'] ?? 0);
            $quantity = (int) $line['quantity'];
            $kind = (string) ($line['kind'] ?? 'product');

            if ($kind === 'set') {
                $set = $sets[$id] ?? null;
                if ($set === null || ! ($set['is_available'] ?? false)) {
                    throw new \InvalidArgumentException(sprintf('Сет #%d недоступен.', $id));
                }
                $base[] = new OrderLine(
                    productId: $id,
                    name: (string) $set['name'],
                    unitPriceKopecks: (int) $set['price_kopecks'],
                    quantity: $quantity,
                    lineType: OrderLineType::Set,
                );

                continue;
            }

            $menuItem = $menuById[$id] ?? null;
            $product = $products[$id] ?? null;
            if ($menuItem === null || ! ($menuItem['is_available'] ?? false) || $product === null) {
                throw new \InvalidArgumentException(sprintf('Товар #%d недоступен в меню филиала.', $id));
            }
            $base[] = new OrderLine(
                productId: $id,
                name: (string) $product['name'],
                unitPriceKopecks: (int) $menuItem['price_kopecks'],
                quantity: $quantity,
                lineType: OrderLineType::Regular,
            );
        }

        return $base;
    }

    /**
     * @return array{0: string, 1: int, 2: bool}
     */
    private function zone(int $branchId, ?string $zoneId): array
    {
        if ($zoneId === null || $zoneId === '') {
            return ['outside', 0, true];
        }

        $zone = $this->deliveryTariffs->findZoneById($branchId, $zoneId);
        if ($zone === null) {
            return ['outside', 0, true];
        }

        return [(string) $zone['id'], (int) $zone['fee_kopecks'], (bool) $zone['is_remote']];
    }
}
