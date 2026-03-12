<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\DeleteClientAddressDTO;
use App\Domain\Client\Entity\Client;

final class DeleteClientAddressUseCase extends ClientBaseUseCase
{
    public function execute(DeleteClientAddressDTO $dto): Client
    {
        return $this->clients->deleteAddress($dto->clientId, $dto->addressId);
    }
}

