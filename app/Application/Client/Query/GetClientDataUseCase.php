<?php

namespace App\Application\Client\Query;

use App\Application\Client\ClientBaseUseCase;
use LogicException;

final class GetClientDataUseCase extends ClientBaseUseCase
{
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

