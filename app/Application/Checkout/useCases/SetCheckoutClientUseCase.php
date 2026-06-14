<?php

namespace App\Application\Checkout\useCases;

use App\Application\Checkout\DTO\SetCheckoutClientDto;
use App\Application\Checkout\Services\CheckoutDraftLifecycle;
use App\Domain\Checkout\Port\ClientProfilePort;
use App\Domain\Checkout\ValueObject\ClientSnapshot;
use App\Domain\Checkout\ValueObject\GuestContact;
use InvalidArgumentException;

/**
 * Сценарий: добавить блок данных о клиенте.
 */
final class SetCheckoutClientUseCase
{
    public function __construct(
        private readonly ClientProfilePort $clientProfiles,
        private readonly CheckoutDraftLifecycle $draftLifecycle,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SetCheckoutClientDto $input): array
    {
        $checkout = $this->draftLifecycle->loadDraft($input->checkoutId);

        $checkout->setClient($this->buildClientSnapshot($input));

        return $this->draftLifecycle->saveAndPresent($checkout);
    }

    private function buildClientSnapshot(SetCheckoutClientDto $input): ClientSnapshot
    {
        if ($input->clientId !== null) {
            $name = $input->name;
            $phone = $input->phone;
            $email = $input->email;

            if ($name === null || $phone === null) {
                $profile = $this->clientProfiles->findRegisteredProfile($input->clientId);

                if ($profile !== null) {
                    $name ??= $profile->name();
                    $phone ??= $profile->phone();
                    $email ??= $profile->email();
                }
            }

            return ClientSnapshot::registered(
                clientId: $input->clientId,
                name: $name,
                phone: $phone,
                email: $email,
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
}
