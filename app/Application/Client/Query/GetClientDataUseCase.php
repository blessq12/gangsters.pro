<?php

namespace App\Application\Client\Query;

use App\Application\Client\ClientBaseUseCase;
use App\Domain\Client\Entity\Client;
use LogicException;

final class GetClientDataUseCase extends ClientBaseUseCase
{
    public function execute(int $clientId): Client
    {
        $client = $this->clients->findById($clientId);

        if ($client === null) {
            throw new LogicException('Client not found');
        }

        return $client;
    }
}

