<?php

namespace App\Domain\Shopping\CartRules\Rules;

use App\Domain\Shopping\CartRules\CartLineItem;
use App\Domain\Shopping\CartRules\CartLineOrigin;
use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\CartRules\RuleEvaluationContext;
use App\Domain\Shopping\CartRules\ShoppingCartRuleInterface;

/**
 * За каждые N единиц «ролла» добавляет системную позицию «комплект»; товар комплекта задаётся флагом на товаре в каталоге.
 */
final class ComplementRule implements ShoppingCartRuleInterface
{
    public const LINE_KEY = 'complement:set';

    public function __construct(
        private readonly array $options = [],
    ) {}

    public function apply(CartState $state, RuleEvaluationContext $context): CartState
    {
        $complementProductIds = array_values(array_unique(array_filter(
            $context->complementProductIds,
            static fn (int $id) => $id > 0,
        )));
        if ($complementProductIds === []) {
            return $this->stripComplementLines($state);
        }

        $rollsPerComplement = (int) ($this->options['rolls_per_complement'] ?? 2);
        if ($rollsPerComplement < 1) {
            $rollsPerComplement = 2;
        }

        $rollQty = 0;
        foreach ($state->userLines as $line) {
            $view = $context->product($line->productId);
            if ($view !== null && $view->countsAsRollUnit) {
                $rollQty += $line->quantity;
            }
        }

        $sets = intdiv($rollQty, $rollsPerComplement);
        $keptSystem = $this->systemLinesWithoutComplement($state->systemLines);

        $newSystem = $keptSystem;
        if ($sets > 0) {
            foreach ($complementProductIds as $complementProductId) {
                if ($context->product($complementProductId) === null) {
                    continue;
                }
                $newSystem[] = new CartLineItem(
                    $complementProductId,
                    $sets,
                    CartLineOrigin::System,
                    self::LINE_KEY.':'.$complementProductId,
                );
            }
        }

        return $state->with(systemLines: $newSystem);
    }

    /**
     * @param  CartLineItem[]  $systemLines
     * @return CartLineItem[]
     */
    private function systemLinesWithoutComplement(array $systemLines): array
    {
        return array_values(array_filter(
            $systemLines,
            static fn (CartLineItem $l) => $l->lineKey !== self::LINE_KEY
                && ! str_starts_with($l->lineKey, self::LINE_KEY.':'),
        ));
    }

    private function stripComplementLines(CartState $state): CartState
    {
        return $state->with(systemLines: $this->systemLinesWithoutComplement($state->systemLines));
    }
}
