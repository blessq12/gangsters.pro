<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\ConfirmCheckoutDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use Illuminate\Support\Facades\Event;

/**
 * Сценарий: подтвердить собранный объект намерения.
 */
final class ConfirmCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly CheckoutPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(ConfirmCheckoutDto $input): array
    {
        $checkout = $this->checkouts->findById(CheckoutId::fromString($input->checkoutId));

        if ($checkout === null) {
            throw CheckoutNotFoundException::forId($input->checkoutId);
        }

        $checkout->confirm();

        $this->checkouts->save($checkout);

        foreach ($checkout->pullRecordedEvents() as $event) {
            Event::dispatch($event);
        }

        return $this->presenter->present($checkout);
    }
}
