<?php

namespace App\Application\Operations\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\DTO\AdminAddClientAddressDTO;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\Events\ClientAddressAdded;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Events\DomainEventBus;

final class AddAdminClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly AdminClientPresenter $presenter,
        private readonly DomainEventBus $events,
    ) {
    }

    public function execute(AdminAddClientAddressDTO $dto): array
    {
        $client = $this->clients->findById($dto->clientId);
        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        $address = ClientAddress::create(
            clientId: $dto->clientId,
            type: $dto->type,
            title: $dto->title,
            street: $dto->street,
            house: $dto->house,
            entrance: $dto->entrance,
            apartment: $dto->apartment,
        );

        $client = $this->clients->addAddress($client->id(), $address, $dto->makeDefault);
        $this->events->publish(new ClientAddressAdded($client, $address));

        return [
            'client' => $this->presenter->presentDetail($client),
        ];
    }
}
