<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\GetCheckoutDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;

final class GetCheckoutUseCase
{
    public function __construct(
        private readonly CheckoutDraftLifecycle $draftLifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(GetCheckoutDto $input): array
    {
        $checkout = $this->draftLifecycle->loadDraft($input->checkoutId);

        return $this->draftLifecycle->saveAndPresent($checkout);
    }
}
