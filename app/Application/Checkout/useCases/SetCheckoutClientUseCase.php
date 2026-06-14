<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutClientDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\GuestContact;
use InvalidArgumentException;

/**
 * Сценарий: добавить блок данных о клиенте.
 */
final class SetCheckoutClientUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutClientDto $input): array
    {
        $checkout = $this->findDraftCheckout($input->checkoutId);

        $checkout->setClient($this->buildClientSnapshot($input));

        $this->checkouts->save($checkout);

        return $this->presenter->present($checkout);
    }

    private function buildClientSnapshot(SetCheckoutClientDto $input): ClientSnapshot
    {
        if ($input->clientId !== null) {
            return ClientSnapshot::registered(
                clientId: $input->clientId,
                name: $input->name,
                phone: $input->phone,
                email: $input->email,
            );
        }

        if ($input->name === null || $input->phone === null) {
            throw new InvalidArgumentException('Для гостя нужны имя и телефон.');
        }

        return ClientSnapshot::guest(
            new GuestContact(
                name: $input->name,
                phone: $input->phone,
                email: $input->email,
            ),
        );
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
