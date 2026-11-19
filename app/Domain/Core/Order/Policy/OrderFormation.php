<?php

namespace App\Domain\Core\Order\Policy;

use App\Domain\Core\Order\Entity\ProductPromotionRole;
use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\Enum\ProductPromotionRoleKind;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderLine;
use DateTimeInterface;

/**
 * Применяет коммерческие правила к позициям и тарифу доставки при оформлении/пересчёте.
 *
 * @phpstan-type FormationResult array{
 *     lines: list<OrderLine>,
 *     delivery: OrderDeliveryInfo
 * }
 */
final class OrderFormation
{
    /**
     * @param  list<OrderLine>  $baseLines  только regular|set
     * @param  list<ProductPromotionRole>  $roles
     * @return FormationResult
     */
    public static function apply(
        array $baseLines,
        OrderDeliveryInfo $delivery,
        PromotionPolicy $policy,
        array $roles,
        string $channel,
        DateTimeInterface $at,
        string $timezone,
    ): array {
        self::assertBaseLines($baseLines);

        $lines = array_values($baseLines);
        $lines = [...$lines, ...self::complementLines($lines, $policy, $roles)];
        $lines = [...$lines, ...self::giftLines($lines, $policy, $roles, $channel, $at, $timezone)];
        $delivery = self::applyDeliveryBenefit($lines, $delivery, $policy);

        return [
            'lines' => $lines,
            'delivery' => $delivery,
        ];
    }

    /**
     * @param  list<OrderLine>  $baseLines
     */
    private static function assertBaseLines(array $baseLines): void
    {
        if ($baseLines === []) {
            throw new \InvalidArgumentException('Заказ нельзя создать с пустой корзиной.');
        }

        foreach ($baseLines as $line) {
            if (! $line instanceof OrderLine) {
                throw new \InvalidArgumentException('Ожидался OrderLine.');
            }
            if (! in_array($line->lineType(), [OrderLineType::Regular, OrderLineType::Set], true)) {
                throw new \InvalidArgumentException(
                    'Базовые позиции заказа могут быть только regular или set.',
                );
            }
        }
    }

    /**
     * @param  list<OrderLine>  $lines
     * @param  list<ProductPromotionRole>  $roles
     * @return list<OrderLine>
     */
    private static function complementLines(
        array $lines,
        PromotionPolicy $policy,
        array $roles,
    ): array {
        $benefit = $policy->complementSetBenefit();
        if (! ($benefit['is_active'] ?? false)) {
            return [];
        }

        $rollsPerSet = max(1, (int) ($benefit['rolls_per_set'] ?? 1));
        $maxSets = max(0, (int) ($benefit['max_sets'] ?? 0));
        if ($maxSets < 1) {
            return [];
        }

        $rollCount = 0;
        foreach ($lines as $line) {
            if (! $line->isBillable()) {
                continue;
            }
            $role = self::roleFor($roles, $line->productId(), ProductPromotionRoleKind::Roll);
            if ($role !== null) {
                $rollCount += $line->quantity();
            }
        }

        $sets = min($maxSets, intdiv($rollCount, $rollsPerSet));
        if ($sets < 1) {
            return [];
        }

        $complements = array_values(array_filter(
            $roles,
            static fn (ProductPromotionRole $role): bool => $role->isComplementSet(),
        ));
        if ($complements === []) {
            return [];
        }

        $result = [];
        foreach ($complements as $complement) {
            $result[] = new OrderLine(
                productId: $complement->productId(),
                name: $complement->name(),
                unitPriceKopecks: 0,
                quantity: $sets,
                lineType: OrderLineType::Complement,
            );
        }

        return $result;
    }

    /**
     * @param  list<OrderLine>  $lines
     * @param  list<ProductPromotionRole>  $roles
     * @return list<OrderLine>
     */
    private static function giftLines(
        array $lines,
        PromotionPolicy $policy,
        array $roles,
        string $channel,
        DateTimeInterface $at,
        string $timezone,
    ): array {
        $rule = $policy->giftRuleForChannel($channel);
        if ($rule === null || ! $policy->isGiftRuleAllowedOnDate($rule, $at, $timezone)) {
            return [];
        }

        $minAmount = (int) ($rule['min_order_amount_kopecks'] ?? 0);
        if (self::billableSubtotal($lines) < $minAmount) {
            return [];
        }

        $gifts = array_values(array_filter(
            $roles,
            static fn (ProductPromotionRole $role): bool => $role->isGiftCandidate(),
        ));
        if ($gifts === []) {
            return [];
        }

        // Один подарок — первый кандидат по роли gift.
        $gift = $gifts[0];

        return [
            new OrderLine(
                productId: $gift->productId(),
                name: $gift->name(),
                unitPriceKopecks: 0,
                quantity: 1,
                lineType: OrderLineType::Gift,
            ),
        ];
    }

    /**
     * @param  list<OrderLine>  $lines
     */
    private static function applyDeliveryBenefit(
        array $lines,
        OrderDeliveryInfo $delivery,
        PromotionPolicy $policy,
    ): OrderDeliveryInfo {
        if (! $delivery->isCourier()) {
            return $delivery->withFee(0);
        }

        $benefit = $policy->deliveryBenefit();
        if (! ($benefit['is_active'] ?? false)) {
            return $delivery;
        }

        $threshold = (int) ($benefit['free_delivery_threshold_kopecks'] ?? 0);
        if ($threshold > 0 && self::billableSubtotal($lines) >= $threshold) {
            return $delivery->withFee(0);
        }

        return $delivery;
    }

    /**
     * @param  list<OrderLine>  $lines
     */
    private static function billableSubtotal(array $lines): int
    {
        $total = 0;
        foreach ($lines as $line) {
            if ($line->isBillable()) {
                $total += $line->lineTotalKopecks();
            }
        }

        return $total;
    }

    /**
     * @param  list<ProductPromotionRole>  $roles
     */
    private static function roleFor(
        array $roles,
        int $productId,
        ProductPromotionRoleKind $kind,
    ): ?ProductPromotionRole {
        foreach ($roles as $role) {
            if ($role->productId() === $productId && $role->role() === $kind) {
                return $role;
            }
        }

        return null;
    }
}
