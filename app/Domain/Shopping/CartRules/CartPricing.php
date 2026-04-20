<?php

namespace App\Domain\Shopping\CartRules;

/**
 * Чистый расчёт итогов и финальных цен строк по каталогу из контекста.
 */
final class CartPricing
{
    private const GIFT_LINE_PREFIX = 'gift:';
    private const COMPLEMENT_LINE_PREFIX = 'complement:';

    public static function apply(CartState $state, RuleEvaluationContext $context): CartState
    {
        $pricedUser = self::priceLines($state->userLines, $context);
        $pricedSystem = self::priceLines($state->systemLines, $context);

        $subUser = self::sumLines($pricedUser);
        $subSystem = self::sumLines($pricedSystem);

        return $state->with(
            userLines: $pricedUser,
            systemLines: $pricedSystem,
            subtotalUserKopecks: $subUser,
            subtotalSystemKopecks: $subSystem,
            grandTotalKopecks: $subUser + $subSystem,
        );
    }

    /**
     * @param  CartLineItem[]  $lines
     * @return CartLineItem[]
     */
    private static function priceLines(array $lines, RuleEvaluationContext $context): array
    {
        $out = [];
        foreach ($lines as $line) {
            $unit = self::resolveUnitPriceKopecks($line, $context);
            $out[] = $line->withFinalUnitPriceKopecks($unit);
        }

        return $out;
    }

    private static function resolveUnitPriceKopecks(CartLineItem $line, RuleEvaluationContext $context): int
    {
        if (
            $line->origin === CartLineOrigin::System
            && (
                str_starts_with($line->lineKey, self::GIFT_LINE_PREFIX)
                || str_starts_with($line->lineKey, self::COMPLEMENT_LINE_PREFIX)
            )
        ) {
            return 0;
        }

        $view = $context->product($line->productId);

        return $view?->priceKopecks ?? 0;
    }

    /**
     * @param  CartLineItem[]  $lines
     */
    private static function sumLines(array $lines): int
    {
        $sum = 0;
        foreach ($lines as $line) {
            $p = $line->finalUnitPriceKopecks ?? 0;
            $sum += $p * $line->quantity;
        }

        return $sum;
    }
}
