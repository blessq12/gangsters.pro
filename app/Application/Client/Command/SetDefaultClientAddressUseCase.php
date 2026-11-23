<?php

namespace App\Application\Client\Command;

use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Supporting\Client\Repository\ClientRepository;

final class SetDefaultClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId, string $addressId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $client->setDefaultAddress($addressId);
        $this->clients->save($client);

        return $this->presenter->present($client);
    }
}
