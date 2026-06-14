<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutPaymentDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Domain\Checkout\ValueObject\PaymentSnapshot;

/**
 * Сценарий: добавить блок данных об оплате.
 */
final class SetCheckoutPaymentUseCase
{
    public function __construct(
        private readonly CheckoutDraftLifecycle $draftLifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutPaymentDto $input): array
    {
        $checkout = $this->draftLifecycle->loadDraft($input->checkoutId);

        $checkout->setPayment(
            new PaymentSnapshot(
                method: $input->method,
                changeFromRubles: $input->changeFromRubles,
            ),
        );

        return $this->draftLifecycle->saveAndPresent($checkout);
    }
}
