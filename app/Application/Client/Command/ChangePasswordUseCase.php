<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\ChangePasswordDTO;
use App\Domain\Client\Entity\Client;
use LogicException;

final class ChangePasswordUseCase extends ClientBaseUseCase
{
    public function execute(ChangePasswordDTO $dto): Client
    {
        $client = $this->clients->findByPasswordResetToken($dto->token);

        if ($client === null) {
            throw new LogicException('Invalid token');
        }

        $client->changePasswordHash($this->hasher->make($dto->password));

        $this->clients->save($client);
        $this->clients->clearPasswordResetToken($client);

        return $client;
    }
}

