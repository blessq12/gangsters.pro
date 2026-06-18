<?php

namespace App\Application\Order\OrderDraft\Support;

use App\Domain\Order\OrderDraft\ValueObject\CartLineSnapshot;

final class PromotionLineClassifier
{
    public static function isGiftLine(CartLineSnapshot $line): bool
    {
        return $line->lineKind() === 'gift';
    }

    public static function isComplementLine(CartLineSnapshot $line): bool
    {
        return $line->lineKind() === 'complement';
    }

    public static function isPromotionSystemLine(CartLineSnapshot $line): bool
    {
        return $line->isPromotionBenefitLine();
    }

    /**
     * @param  list<CartLineSnapshot>  $lines
     * @return list<CartLineSnapshot>
     */
    public static function userLines(array $lines): array
    {
        return array_values(array_filter(
            $lines,
            static fn (CartLineSnapshot $line): bool => ! self::isPromotionSystemLine($line),
        ));
    }

    /**
     * @param  list<CartLineSnapshot>  $lines
     * @return list<CartLineSnapshot>
     */
    public static function linesWithoutComplement(array $lines): array
    {
        return array_values(array_filter(
            $lines,
            static fn (CartLineSnapshot $line): bool => ! self::isComplementLine($line),
        ));
    }

    /**
     * @param  list<CartLineSnapshot>  $lines
     * @return list<CartLineSnapshot>
     */
    public static function linesWithoutGift(array $lines): array
    {
        return array_values(array_filter(
            $lines,
            static fn (CartLineSnapshot $line): bool => ! self::isGiftLine($line),
        ));
    }
}
