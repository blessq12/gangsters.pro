<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\AddClientAddressDTO;
use App\Domain\Client\Entity\ClientAddress;
use LogicException;

final class AddClientAddressUseCase extends ClientBaseUseCase
{
    public function execute(AddClientAddressDTO $dto): array
    {
        $clientId = $this->authContext->currentClientId();

        $client = $this->clients->findById($clientId);

        if ($client === null) {
            throw new LogicException('Client not found');
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

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

