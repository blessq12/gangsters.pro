<?php

namespace App\Application\Operations\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\DTO\AdminDeleteClientAddressDTO;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\Events\ClientAddressDeleted;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Events\DomainEventBus;

final class DeleteAdminClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly AdminClientPresenter $presenter,
        private readonly DomainEventBus $events,
    ) {
    }

    public function execute(AdminDeleteClientAddressDTO $dto): array
    {
        $client = $this->clients->findById($dto->clientId);
        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        $deletedAddress = null;
        foreach ($client->addresses() as $address) {
            if ($address->id() === $dto->addressId) {
                $deletedAddress = $address;
                break;
            }
        }

        if ($deletedAddress === null) {
            throw new ApiException('Address not found.', 404);
        }

        $client = $this->clients->deleteAddress($dto->clientId, $dto->addressId);
        $this->events->publish(new ClientAddressDeleted($client, $deletedAddress));

        return [
            'client' => $this->presenter->presentDetail($client),
        ];
    }
}
