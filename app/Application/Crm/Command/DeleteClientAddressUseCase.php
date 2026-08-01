<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class DeleteClientAddressUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array{client: array<string, mixed>}
     */
    public function execute(int $clientId, string $addressId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        $client->removeAddress($addressId);
        $this->clients->save($client);

        return ['client' => $this->presenter->present($client)];
    }
}
