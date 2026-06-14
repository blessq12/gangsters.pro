<?php

namespace App\Application\Checkout\Services;

use App\Application\Checkout\Presenter\CheckoutPresenter;
use App\Domain\Checkout\Entity\Checkout;
use App\Domain\Checkout\Exception\CheckoutNotFoundException;
use App\Domain\Checkout\Repository\CheckoutRepository;
use App\Domain\Checkout\ValueObject\CheckoutId;
use Illuminate\Support\Facades\Event;

/**
 * Единый pipeline черновика: sync benefits → save → present / confirm.
 */
final class CheckoutDraftLifecycle
{
    public function __construct(
        private readonly SyncCheckoutComplementBenefitLines $complementBenefitLines,
        private readonly SyncCheckoutGiftBenefitLines $giftBenefitLines,
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
        $this->syncBenefitLines($checkout);
        $this->checkouts->save($checkout);

        return $this->presenter->present($checkout);
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmAndPresent(Checkout $checkout): array
    {
        $this->syncBenefitLines($checkout);
        $this->giftBenefitLines->assertValidForConfirm($checkout);
        $checkout->confirm();
        $this->checkouts->save($checkout);

        foreach ($checkout->pullRecordedEvents() as $event) {
            Event::dispatch($event);
        }

        return $this->presenter->present($checkout);
    }

    private function syncBenefitLines(Checkout $checkout): void
    {
        $this->complementBenefitLines->sync($checkout);
        $this->giftBenefitLines->sync($checkout);
    }
}
