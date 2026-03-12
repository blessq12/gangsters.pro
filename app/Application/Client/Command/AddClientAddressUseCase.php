<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\AddClientAddressDTO;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use LogicException;

final class AddClientAddressUseCase extends ClientBaseUseCase
{
    public function execute(AddClientAddressDTO $dto): Client
    {
        $client = $this->clients->findById($dto->clientId);

        if ($client === null) {
            throw new LogicException('Client not found');
        }

        $address = ClientAddress::create(
            clientId: $dto->clientId,
            type: $dto->type,
            title: $dto->title,
            street: $dto->street,
            house: $dto->house,
            liter: $dto->liter,
            staircase: $dto->staircase,
            apartment: $dto->apartment,
            entranceCode: $dto->entranceCode,
            floor: $dto->floor,
            comment: $dto->comment,
        );

        return $this->clients->addAddress($client->id(), $address, $dto->makeDefault);
    }
}

