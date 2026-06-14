<?php

namespace App\Application\Checkout\Support;

use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Enum\CheckoutStatus;

/**
 * Состояние визарда оформления для API snapshot.
 */
final class CheckoutWizardResolver
{
    /**
     * @return array{
     *     suggested_step: string|null,
     *     can_confirm: bool,
     *     missing_blocks: list<string>
     * }
     */
    public function resolve(Checkout $checkout): array
    {
        if ($checkout->status() !== CheckoutStatus::Draft) {
            return [
                'suggested_step' => null,
                'can_confirm' => false,
                'missing_blocks' => [],
            ];
        }

        $missingBlocks = $this->missingBlocks($checkout);

        return [
            'suggested_step' => $this->suggestStep($checkout, $missingBlocks),
            'can_confirm' => $missingBlocks === [],
            'missing_blocks' => $missingBlocks,
        ];
    }

    /**
     * @return list<string>
     */
    private function missingBlocks(Checkout $checkout): array
    {
        $missing = [];

        if (! $checkout->cart()->hasItems()) {
            $missing[] = 'cart';
        }

        if ($checkout->client() === null) {
            $missing[] = 'client';
        }

        if ($checkout->delivery() === null) {
            $missing[] = 'delivery';
        }

        if ($checkout->payment() === null) {
            $missing[] = 'payment';
        }

        return $missing;
    }

    /**
     * @param  list<string>  $missingBlocks
     */
    private function suggestStep(Checkout $checkout, array $missingBlocks): ?string
    {
        if ($missingBlocks === [] || $missingBlocks === ['cart']) {
            if ($missingBlocks === ['cart']) {
                return null;
            }

            return $checkout->cart()->hasItems() ? 'confirm' : null;
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
