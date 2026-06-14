<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\UpdateCheckoutCartDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use InvalidArgumentException;

/**
 * Сценарий: обновить блок корзины (добавление / удаление / изменение количества).
 */
final class UpdateCheckoutCartUseCase
{
    public function __construct(
        private readonly CatalogPricingPort $pricing,
        private readonly CheckoutDraftLifecycle $draftLifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateCheckoutCartDto $input): array
    {
        $checkout = $this->draftLifecycle->loadDraft($input->checkoutId);

        if ($input->quantity === 0) {
            $checkout->removeCartLine($input->productId);
        } else {
            $quote = $this->pricing->findActiveProductQuote($input->productId);

            if ($quote === null) {
                throw new InvalidArgumentException('Товар недоступен для добавления в корзину.');
            }

            $checkout->upsertCartLine(
                CartLineSnapshot::fromQuote($quote, $input->quantity, $input->payload),
            );
        }

        return $this->draftLifecycle->saveAndPresent($checkout);
    }
}
