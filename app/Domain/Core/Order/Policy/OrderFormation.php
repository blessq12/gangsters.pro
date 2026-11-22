<?php

namespace App\Domain\Core\Order\Policy;

use App\Domain\Core\Order\Enum\OrderLineType;
use App\Domain\Core\Order\ValueObject\OrderDeliveryInfo;
use App\Domain\Core\Order\ValueObject\OrderFormationFacts;
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
     * @return FormationResult
     */
    public static function apply(
        array $baseLines,
        OrderDeliveryInfo $delivery,
        PromotionPolicy $policy,
        OrderFormationFacts $facts,
        string $channel,
        DateTimeInterface $at,
        string $timezone,
        bool $zoneIsRemote = false,
    ): array {
        self::assertBaseLines($baseLines);

        $lines = array_values($baseLines);
        $lines = [...$lines, ...self::complementLines($lines, $policy, $facts)];
        $lines = [...$lines, ...self::giftLines($lines, $policy, $facts, $channel, $at, $timezone)];
        $delivery = self::applyDeliveryBenefit($lines, $delivery, $policy, $zoneIsRemote);

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
     * @return list<OrderLine>
     */
    private static function complementLines(
        array $lines,
        PromotionPolicy $policy,
        OrderFormationFacts $facts,
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
            if ($facts->isRoll($line->productId())) {
                $rollCount += $line->quantity();
            }
        }

        $sets = min($maxSets, intdiv($rollCount, $rollsPerSet));
        if ($sets < 1) {
            return [];
        }

        $complements = $facts->complements();
        if ($complements === []) {
            return [];
        }

        $result = [];
        foreach ($complements as $complement) {
            $result[] = new OrderLine(
                productId: (int) $complement['product_id'],
                name: (string) $complement['name'],
                unitPriceKopecks: 0,
                quantity: $sets,
                lineType: OrderLineType::Complement,
            );
        }

        return $result;
    }

    /**
     * @param  list<OrderLine>  $lines
     * @return list<OrderLine>
     */
    private static function giftLines(
        array $lines,
        PromotionPolicy $policy,
        OrderFormationFacts $facts,
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

        $gift = $facts->gift();
        if ($gift === null) {
            return [];
        }

        return [
            new OrderLine(
                productId: (int) $gift['product_id'],
                name: (string) $gift['name'],
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
        bool $zoneIsRemote,
    ): OrderDeliveryInfo {
        if (! $delivery->isCourier()) {
            return $delivery->withFee(0);
        }

        $benefit = $policy->deliveryBenefit();
        if (! ($benefit['is_active'] ?? false)) {
            return $delivery;
        }

        $threshold = (int) ($benefit['free_delivery_threshold_kopecks'] ?? 0);
        $atThreshold = self::billableSubtotal($lines) >= $threshold;
        $mode = ! $atThreshold
            ? (string) ($benefit['below_threshold_fee_mode'] ?? 'base_tariff')
            : ($zoneIsRemote
                ? (string) ($benefit['outside_zone_at_threshold_fee_mode'] ?? 'outside_zone_surcharge_only')
                : (string) ($benefit['in_zone_at_threshold_fee_mode'] ?? 'free'));

        // Надбавка вне зоны — ответственность Delivery, не Promotion Policy.
        return $delivery->withFee(self::feeByMode(
            $mode,
            $delivery->feeKopecks(),
            0,
        ));
    }

    private static function feeByMode(string $mode, int $baseFeeKopecks, int $surchargeKopecks): int
    {
        return match ($mode) {
            'base_tariff' => $baseFeeKopecks,
            'free' => 0,
            'outside_zone_surcharge_only' => $surchargeKopecks,
            'base_plus_surcharge' => $baseFeeKopecks + $surchargeKopecks,
            default => throw new \InvalidArgumentException('Неизвестный режим тарифа доставки: '.$mode),
        };
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
}
