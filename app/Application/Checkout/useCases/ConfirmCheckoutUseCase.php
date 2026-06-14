<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\ConfirmCheckoutDto;
use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Application\Checkout\Services\ApplyCheckoutBenefitRules;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use Illuminate\Support\Facades\Event;

/**
 * Сценарий: финальное применение бенефитов → подтверждение → создание заказа по событию.
 */
final class ConfirmCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutRepository $checkouts,
        private readonly ApplyCheckoutBenefitRules $benefitRules,
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

        $this->benefitRules->apply($checkout);
        $this->checkouts->save($checkout);

        $checkout->confirm();

        $this->checkouts->save($checkout);

        foreach ($checkout->pullRecordedEvents() as $event) {
            Event::dispatch($event);
        }

        return $this->presenter->present($checkout);
    }
}
