<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutPaymentDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;

/**
 * Сценарий: добавить блок данных об оплате.
 */
final class SetCheckoutPaymentUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutPaymentDto $input): array
    {
        $checkout = $this->findDraftCheckout($input->checkoutId);

        $checkout->setPayment(
            new PaymentSnapshot(
                method: $input->method,
                changeFromRubles: $input->changeFromRubles,
            ),
        );

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
