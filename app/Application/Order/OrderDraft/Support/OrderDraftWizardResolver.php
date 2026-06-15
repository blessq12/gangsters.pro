<?php

namespace App\Application\Order\OrderDraft\Support;

use App\Domain\Order\OrderDraft\Entity\OrderDraft;

/**
 * Состояние визарда оформления для API snapshot.
 */
final class OrderDraftWizardResolver
{
    /**
     * @return array{
     *     suggested_step: string|null,
     *     can_confirm: bool,
     *     missing_blocks: list<string>
     * }
     */
    public function resolve(OrderDraft $draft): array
    {
        $missingBlocks = $this->missingBlocks($draft);

        return [
            'suggested_step' => $this->suggestStep($draft, $missingBlocks),
            'can_confirm' => $missingBlocks === [],
            'missing_blocks' => $missingBlocks,
        ];
    }

    /**
     * @return list<string>
     */
    private function missingBlocks(OrderDraft $draft): array
    {
        $missing = [];

        if (! $draft->cart()->hasItems()) {
            $missing[] = 'cart';
        }

        if ($draft->client() === null) {
            $missing[] = 'client';
        }

        if ($draft->delivery() === null) {
            $missing[] = 'delivery';
        }

        if ($draft->payment() === null) {
            $missing[] = 'payment';
        }

        return $missing;
    }

    /**
     * @param  list<string>  $missingBlocks
     */
    private function suggestStep(OrderDraft $draft, array $missingBlocks): ?string
    {
        if ($missingBlocks === [] || $missingBlocks === ['cart']) {
            if ($missingBlocks === ['cart']) {
                return null;
            }

            return $draft->cart()->hasItems() ? 'confirm' : null;
        }

        if (in_array('client', $missingBlocks, true)) {
            return 'guest';
        }

        if (in_array('delivery', $missingBlocks, true)) {
            return 'delivery';
        }

        if (in_array('payment', $missingBlocks, true)) {
            return 'payment';
        }

        return null;
    }
}
