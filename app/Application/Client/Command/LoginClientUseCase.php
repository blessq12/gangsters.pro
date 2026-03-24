<?php

namespace App\Application\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\LoginDTO;
use App\Domain\Client\Events\ClientLoginFailed;

final class LoginClientUseCase extends ClientBaseUseCase
{
    public function execute(LoginDTO $dto): array
    {
        $identifier = $dto->phone ?? $dto->email;

        if ($identifier === null || $identifier === '') {
            throw new ApiException('Phone or email is required');
        }

        $client = null;

        if ($dto->phone !== null) {
            $client = $this->clients->findByPhone($dto->phone);
        } elseif ($dto->email !== null) {
            $client = $this->clients->findByEmail($dto->email);
        }

        if ($client === null) {
            $this->events->publish(new ClientLoginFailed($identifier, 'not_found'));
            throw new ApiException('Invalid credentials');
        }

        $hash = $client->passwordHash();

        if ($hash === null || !$this->hasher->check($dto->password, $hash)) {
            $this->events->publish(new ClientLoginFailed($identifier, 'wrong_password'));
            throw new ApiException('Invalid credentials');
        }

        if (!$client->isActive()) {
            $this->events->publish(new ClientLoginFailed($identifier, 'blocked'));
            throw new ApiException('Client is blocked or deleted');
        }

        $token = $this->tokens->issueTokenForClient($client->id());

        return [
            'client' => $this->presenter->present($client),
            'token' => $token,
        ];
    }
}

