<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutDeliveryDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Domain\Checkout\Enum\DeliveryMethod;
use App\Domain\Checkout\ValueObject\DeliverySnapshot;
use InvalidArgumentException;

/**
 * Сценарий: добавить блок данных о доставке.
 */
final class SetCheckoutDeliveryUseCase
{
    public function __construct(
        private readonly CheckoutDraftLifecycle $draftLifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutDeliveryDto $input): array
    {
        $checkout = $this->draftLifecycle->loadDraft($input->checkoutId);

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

        return $this->draftLifecycle->saveAndPresent($checkout);
    }
}
