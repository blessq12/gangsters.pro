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
            $profile = $this->clientProfiles->findRegisteredProfile($input->clientId);

            if ($profile === null) {
                throw new InvalidArgumentException('Клиент с указанным идентификатором не найден.');
            }

            return ClientSnapshot::registered(
                clientId: $input->clientId,
                name: $input->name ?? $profile->name(),
                phone: $input->phone ?? $profile->phone(),
                email: $input->email ?? $profile->email(),
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
