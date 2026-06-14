<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutDeliveryDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use InvalidArgumentException;

/**
 * Сценарий: добавить блок данных о доставке.
 */
final class SetCheckoutDeliveryUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutDeliveryDto $input): array
    {
        $checkout = $this->findDraftCheckout($input->checkoutId);

        if ($input->method === DeliveryMethod::Courier && $input->address === null) {
            throw new InvalidArgumentException('Для курьерской доставки нужен адрес.');
        }

        $checkout->setDelivery(
            new DeliverySnapshot(
                method: $input->method,
                address: $input->address,
                comment: $input->comment,
                scheduledAt: $input->scheduledAt,
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
