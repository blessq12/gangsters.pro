<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use Illuminate\Auth\AuthenticationException;

final class GetClientProfileUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array{client: array<string, mixed>}
     */
    public function execute(int $clientId): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        return ['client' => $this->presenter->present($client)];
    }
}
