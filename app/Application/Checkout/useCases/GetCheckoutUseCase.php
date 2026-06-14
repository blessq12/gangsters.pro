<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\GetCheckoutDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;

final class GetCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(GetCheckoutDto $input): array
    {
        $checkout = $this->checkouts->findById(CheckoutId::fromString($input->checkoutId));

        if ($checkout === null) {
            throw CheckoutNotFoundException::forId($input->checkoutId);
        }

        return $this->presenter->present($checkout);
    }
}
