<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\RequestPasswordResetDTO;
use LogicException;

final class RequestPasswordResetUseCase extends ClientBaseUseCase
{
    public function execute(RequestPasswordResetDTO $dto): string
    {
        $client = $this->clients->findByEmail($dto->email);

        if ($client === null) {
            throw new LogicException('Client not found');
        }

        $token = bin2hex(random_bytes(16));

        $this->clients->setPasswordResetToken($dto->email, $token);

        return $token;
    }
}

