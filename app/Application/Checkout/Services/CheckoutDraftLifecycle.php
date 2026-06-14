<?php

namespace App\Application\Checkout\Services;

use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;

/**
 * Завершение шага заполнения черновика: применить бенефиты → сохранить → отдать snapshot.
 */
final class CheckoutDraftLifecycle
{
    public function __construct(
        private readonly ApplyCheckoutBenefitRules $benefitRules,
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    public function loadDraft(string $checkoutId): Checkout
    {
        $checkout = $this->checkouts->findById(CheckoutId::fromString($checkoutId));

        if ($checkout === null) {
            throw CheckoutNotFoundException::forId($checkoutId);
        }

        return $checkout;
    }

    /**
     * @return array<string, mixed>
     */
    public function saveAndPresent(Checkout $checkout): array
    {
        $this->benefitRules->apply($checkout);
        $this->checkouts->save($checkout);

        return $this->presenter->present($checkout);
    }
}
