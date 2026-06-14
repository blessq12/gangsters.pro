<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\UpdateCheckoutCartDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Port\CatalogPricingPort;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CartLineSnapshot;
use App\Domain\Checkout\ValueObject\CheckoutId;
use InvalidArgumentException;

/**
 * Сценарий: обновить блок корзины (добавление / удаление / изменение количества).
 */
final class UpdateCheckoutCartUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CatalogPricingPort $pricing,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateCheckoutCartDto $input): array
    {
        $checkout = $this->findDraftCheckout($input->checkoutId);

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

        $this->checkouts->save($checkout);

        return $this->presenter->present($checkout);
    }

    private function findDraftCheckout(string $checkoutId): Checkout
    {
        $checkout = $this->checkouts->findById(CheckoutId::fromString($checkoutId));

        if ($checkout === null) {
            throw CheckoutNotFoundException::forId($checkoutId);
        }

        return $checkout;
    }
}
