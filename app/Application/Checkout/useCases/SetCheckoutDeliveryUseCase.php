<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutDeliveryDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Application\Checkout\Services\PrepareCheckoutDeliveryAddress;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;

/**
 * Сценарий: добавить блок данных о доставке.
 */
final class SetCheckoutDeliveryUseCase
{
    public function __construct(
        private readonly CheckoutDraftLifecycle $draftLifecycle,
        private readonly PrepareCheckoutDeliveryAddress $prepareDeliveryAddress,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutDeliveryDto $input): array
    {
        $checkout = $this->draftLifecycle->loadDraft($input->checkoutId);

        $preparedAddress = $this->prepareDeliveryAddress->prepare(
            method: $input->method,
            address: $input->address,
        );

        $checkout->setDelivery(
            new DeliverySnapshot(
                method: $input->method,
                address: $preparedAddress,
                comment: $input->comment,
                scheduledAt: $input->scheduledAt,
            ),
        );

        return $this->draftLifecycle->saveAndPresent($checkout);
    }
}
