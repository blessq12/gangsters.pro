<?php

namespace App\Application\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\DeleteClientAddressDTO;
use App\Domain\Client\Events\ClientAddressDeleted;

final class DeleteClientAddressUseCase extends ClientBaseUseCase
{
    public function execute(DeleteClientAddressDTO $dto): array
    {
        $clientId = $this->authContext->currentClientId();
        $currentClient = $this->clients->findById($clientId);
        if ($currentClient === null) {
            throw new ApiException('Client not found');
        }

        $deletedAddress = null;
        foreach ($currentClient->addresses() as $address) {
            if ($address->id() === $dto->addressId) {
                $deletedAddress = $address;
                break;
            }
        }

        $client = $this->clients->deleteAddress($clientId, $dto->addressId);
        if ($deletedAddress !== null) {
            $this->events->publish(new ClientAddressDeleted($client, $deletedAddress));
        }

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

