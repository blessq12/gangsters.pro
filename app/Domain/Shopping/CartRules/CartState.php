<?php

namespace App\Domain\Shopping\CartRules;

/**
 * Неизменяемый снимок корзины после правил и расчёта цен.
 *
 * @param  CartLineItem[]  $userLines
 * @param  CartLineItem[]  $systemLines
 * @param  array<string, mixed>  $promoState
 */
final readonly class CartState
{
    /**
     * @param  CartLineItem[]  $userLines
     * @param  CartLineItem[]  $systemLines
     * @param  array<string, mixed>  $promoState
     */
    public function __construct(
        public array $userLines,
        public array $systemLines,
        public array $promoState,
        public int $subtotalUserKopecks,
        public int $subtotalSystemKopecks,
        public int $grandTotalKopecks,
    ) {}

    /**
     * @param  CartLineItem[]  $userLines
     * @param  CartLineItem[]  $systemLines
     * @param  array<string, mixed>  $promoState
     */
    public function with(
        ?array $userLines = null,
        ?array $systemLines = null,
        ?array $promoState = null,
        ?int $subtotalUserKopecks = null,
        ?int $subtotalSystemKopecks = null,
        ?int $grandTotalKopecks = null,
    ): self {
        return new self(
            $userLines ?? $this->userLines,
            $systemLines ?? $this->systemLines,
            $promoState ?? $this->promoState,
            $subtotalUserKopecks ?? $this->subtotalUserKopecks,
            $subtotalSystemKopecks ?? $this->subtotalSystemKopecks,
            $grandTotalKopecks ?? $this->grandTotalKopecks,
        );
    }
}
