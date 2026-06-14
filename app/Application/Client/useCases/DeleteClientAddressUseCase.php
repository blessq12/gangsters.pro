<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\DeleteClientAddressDto;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientAddressId;
use App\Domain\Client\ValueObject\ClientId;

/**
 * Сценарий: удалить адрес из адресной книги клиента.
 */
final class DeleteClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(DeleteClientAddressDto $input): array
    {
        $client = $this->findClient($input->clientId);

        $client->removeAddress(ClientAddressId::fromInt($input->addressId));

        $this->clients->save($client);

        return $this->presenter->present($client);
    }

    private function findClient(int $clientId): Client
    {
        $client = $this->clients->findById(ClientId::fromInt($clientId));

        if ($client === null) {
            throw ClientNotFoundException::forId($clientId);
        }

        return $client;
    }
}
