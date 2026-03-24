<?php

namespace App\Application\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\AddClientAddressDTO;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\Events\ClientAddressAdded;

final class AddClientAddressUseCase extends ClientBaseUseCase
{
    public function execute(AddClientAddressDTO $dto): array
    {
        $clientId = $this->authContext->currentClientId();

        $client = $this->clients->findById($clientId);

        if ($client === null) {
            throw new ApiException('Client not found');
        }

        $address = ClientAddress::create(
            clientId: $clientId,
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
            'client' => $this->presenter->present($client),
        ];
    }
}

