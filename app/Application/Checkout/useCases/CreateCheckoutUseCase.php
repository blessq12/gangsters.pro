<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\CreateCheckoutDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\ValueObject\CheckoutId;

/**
 * Сценарий: создать объект чекаута с идентификатором.
 */
final class CreateCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutDraftLifecycle $draftLifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CreateCheckoutDto $input): array
    {
        $checkout = Checkout::create(CheckoutId::generate());

        return $this->draftLifecycle->saveAndPresent($checkout);
    }
}
