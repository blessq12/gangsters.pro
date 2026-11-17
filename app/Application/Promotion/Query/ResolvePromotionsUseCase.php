<?php

namespace App\Application\Promotion\Query;

use App\Application\Promotion\DTO\ResolvePromotionsDto;
use App\Application\Promotion\Presenter\AppliedPromotionsPresenter;
use App\Domain\Promotion\Entity\ProductPromotionRole;
use App\Domain\Promotion\Entity\PromotionPolicy;
use App\Domain\Promotion\Enum\ProductPromotionRoleKind;
use App\Domain\Promotion\Repository\ProductPromotionRoleRepository;
use App\Domain\Promotion\Repository\PromotionPolicyRepository;
use App\Domain\Promotion\Repository\PromotionZoneRepository;
use App\Shared\Enum\DeliveryMethod;

/**
 * Резолв акций только из таблиц Promotion (без Catalog/Delivery).
 */
final class ResolvePromotionsUseCase
{
    public function __construct(
        private readonly PromotionPolicyRepository $policies,
        private readonly ProductPromotionRoleRepository $roles,
        private readonly PromotionZoneRepository $zones,
        private readonly AppliedPromotionsPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ResolvePromotionsDto $dto): array
    {
        if ($dto->currentTotalKopecks < 0) {
            throw new \InvalidArgumentException('current_total_kopecks не может быть отрицательным.');
        }

        $policy = $this->policies->find();
        if ($policy === null) {
            throw new \InvalidArgumentException('Политика акций не найдена.');
        }

        $qtyByProduct = [];
        foreach ($dto->lines as $line) {
            if (! is_array($line)) {
                continue;
            }
            $productId = (int) ($line['product_id'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);
            if ($productId < 1 || $quantity < 1) {
                continue;
            }
            $qtyByProduct[$productId] = ($qtyByProduct[$productId] ?? 0) + $quantity;
        }

        $cartRoles = $this->roles->findByProductIds(array_keys($qtyByProduct));
        $rollCount = 0;
        foreach ($cartRoles as $role) {
            if ($role->countsAsRoll()) {
                $rollCount += $qtyByProduct[$role->productId()] ?? 0;
            }
        }

        $gift = $this->resolveGift($policy, $dto);
        $complements = $this->resolveComplements($policy, $rollCount);
        [$freeDelivery, $discount] = $this->resolveFreeDelivery($policy, $dto);

        return $this->presenter->present([
            'gift' => $gift,
            'complements' => $complements,
            'free_delivery' => $freeDelivery,
            'delivery_discount_kopecks' => $discount,
            'roll_count' => $rollCount,
            'complement_entitled' => $this->entitledComplementSets($policy, $rollCount),
        ]);
    }

    /**
     * @return array{product_id: int, name: string, price_kopecks: int}|null
     */
    private function resolveGift(
        PromotionPolicy $policy,
        ResolvePromotionsDto $dto,
    ): ?array {
        $channel = $this->normalizeChannel($dto->deliveryMethod);
        $rule = $policy->giftRuleForChannel($channel);
        if ($rule === null) {
            return null;
        }

        $at = $dto->scheduledAt ?? new \DateTimeImmutable('now');
        $timezone = (string) config('app.timezone', 'UTC');
        if (! $policy->isGiftRuleAllowedOnDate($rule, $at, $timezone)) {
            return null;
        }

        if ($dto->currentTotalKopecks < (int) ($rule['min_order_amount_kopecks'] ?? 0)) {
            return null;
        }

        $candidates = $this->roles->findByRole(ProductPromotionRoleKind::Gift);
        if ($candidates === []) {
            return null;
        }

        /** @var ProductPromotionRole $gift */
        $gift = $candidates[0];

        return [
            'product_id' => $gift->productId(),
            'name' => $gift->name(),
            'price_kopecks' => $gift->priceKopecks(),
        ];
    }

    /**
     * @return list<array{product_id: int, name: string, quantity: int, price_kopecks: int}>
     */
    private function resolveComplements(
        PromotionPolicy $policy,
        int $rollCount,
    ): array {
        $entitled = $this->entitledComplementSets($policy, $rollCount);
        if ($entitled < 1) {
            return [];
        }

        $complements = $this->roles->findByRole(ProductPromotionRoleKind::Complement);
        if ($complements === []) {
            return [];
        }

        $first = $complements[0];

        return [[
            'product_id' => $first->productId(),
            'name' => $first->name(),
            'quantity' => $entitled,
            'price_kopecks' => $first->priceKopecks(),
        ]];
    }

    private function entitledComplementSets(
        PromotionPolicy $policy,
        int $rollCount,
    ): int {
        $benefit = $policy->complementSetBenefit();
        if (! ($benefit['is_active'] ?? false)) {
            return 0;
        }

        $rollsPerSet = max(1, (int) ($benefit['rolls_per_set'] ?? 1));
        $maxSets = max(1, (int) ($benefit['max_sets'] ?? 1));

        return min(intdiv($rollCount, $rollsPerSet), $maxSets);
    }

    /**
     * @return array{0: bool, 1: int}
     */
    private function resolveFreeDelivery(
        PromotionPolicy $policy,
        ResolvePromotionsDto $dto,
    ): array {
        if ($this->normalizeChannel($dto->deliveryMethod) !== 'courier') {
            return [false, 0];
        }

        $benefit = $policy->deliveryBenefit();
        if (! ($benefit['is_active'] ?? false)) {
            return [false, 0];
        }

        $threshold = (int) ($benefit['free_delivery_threshold_kopecks'] ?? 0);
        if ($dto->currentTotalKopecks < $threshold) {
            return [false, 0];
        }

        $zoneId = $dto->deliveryZoneId !== null ? trim($dto->deliveryZoneId) : '';
        if ($zoneId === '') {
            return [false, 0];
        }

        $zone = $this->zones->findById($zoneId);
        if ($zone === null) {
            return [false, 0];
        }

        if ($zone->isRemote()) {
            return [false, 0];
        }

        return [true, $zone->feeKopecks()];
    }

    private function normalizeChannel(string $deliveryMethod): string
    {
        $method = DeliveryMethod::tryFrom($deliveryMethod);

        return match ($method) {
            DeliveryMethod::Pickup => 'pickup',
            DeliveryMethod::Courier => 'courier',
            null => $deliveryMethod === 'pickup' ? 'pickup' : 'courier',
        };
    }
}
