<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\DeleteClientAddressDTO;

final class DeleteClientAddressUseCase extends ClientBaseUseCase
{
    public function execute(DeleteClientAddressDTO $dto): array
    {
        $clientId = $this->authContext->currentClientId();

        $client = $this->clients->deleteAddress($clientId, $dto->addressId);

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

