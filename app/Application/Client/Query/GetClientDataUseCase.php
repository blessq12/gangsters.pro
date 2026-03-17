<?php

namespace App\Application\Client\Query;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\Presenter\ClientPresenter;
use LogicException;

final class GetClientDataUseCase extends ClientBaseUseCase
{
    public function __construct(
        ClientRepository $clients,
        Hasher $hasher,
        ClientAuthContext $authContext,
        ClientTokenService $tokens,
        private readonly ClientPresenter $presenter,
    ) {
        parent::__construct($clients, $hasher, $authContext, $tokens);
    }

    public function execute(?int $clientId = null): array
    {
        $id = $clientId ?? $this->authContext->currentClientId();

        $client = $this->clients->findById($id);

        if ($client === null) {
            throw new LogicException('Client not found');
        }

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

