<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\RequestPasswordResetDTO;

final class RequestPasswordResetUseCase extends ClientBaseUseCase
{
    public function execute(RequestPasswordResetDTO $dto): void
    {
        $client = $this->clients->findByEmail($dto->email);

        if ($client === null) {
            return;
        }

        $token = bin2hex(random_bytes(16));

        $this->clients->setPasswordResetToken($dto->email, $token);
    }
}

