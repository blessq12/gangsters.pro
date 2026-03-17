<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\LoginDTO;
use LogicException;

final class LoginClientUseCase extends ClientBaseUseCase
{
    public function execute(LoginDTO $dto): array
    {
        $identifier = $dto->phone ?? $dto->email;

        if ($identifier === null || $identifier === '') {
            throw new LogicException('Phone or email is required');
        }

        $client = null;

        if ($dto->phone !== null) {
            $client = $this->clients->findByPhone($dto->phone);
        } elseif ($dto->email !== null) {
            $client = $this->clients->findByEmail($dto->email);
        }

        if ($client === null) {
            throw new LogicException('Invalid credentials');
        }

        $hash = $client->passwordHash();

        if ($hash === null || !$this->hasher->check($dto->password, $hash)) {
            throw new LogicException('Invalid credentials');
        }

        if (!$client->isActive()) {
            throw new LogicException('Client is blocked or deleted');
        }

        $token = $this->tokens->issueTokenForClient($client->id());

        return [
            'client' => $this->presenter->present($client),
            'token' => $token,
        ];
    }
}

