<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\CreateCheckoutDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;

/**
 * Сценарий: создать объект чекаута с идентификатором.
 */
final class CreateCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateCheckoutDto $input): array
    {
        $checkout = Checkout::create(CheckoutId::generate());

        $this->checkouts->save($checkout);

        return $this->presenter->present($checkout);
    }
}
