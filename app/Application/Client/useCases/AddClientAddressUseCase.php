<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\AddClientAddressDto;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientId;

/**
 * Сценарий: добавить адрес в адресную книгу клиента.
 */
final class AddClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(AddClientAddressDto $input): array
    {
        $client = $this->findClient($input->clientId);

        $client->addAddress(
            ClientAddress::create(
                type: $input->type,
                title: $input->title,
                street: trim($input->street),
                house: trim($input->house),
                entrance: $input->entrance,
                apartment: $input->apartment,
                comment: $input->comment,
                makeDefault: $input->makeDefault,
            ),
        );

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
