<?php

namespace App\Application\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\RegisterDTO;
use App\Domain\Client\Events\ClientRegistered;

final class RegisterClientUseCase extends ClientBaseUseCase
{
    public function execute(RegisterDTO $dto): array
    {
        if ($this->clients->existsByPhone($dto->phone)) {
            throw new ApiException('Client with this phone already exists');
        }

        if ($dto->email !== null && $this->clients->existsByEmail($dto->email)) {
            throw new ApiException('Client with this email already exists');
        }

        $client = $this->factory->createNew(
            name: $dto->name,
            phone: $dto->phone,
            email: $dto->email,
            birthDate: $dto->birthDate,
            passwordHash: $dto->password !== null ? $this->hasher->make($dto->password) : null,
            consentPersonalData: $dto->consentPersonalData,
            consentMarketing: $dto->consentMarketing,
        );

        $this->clients->save($client);
        $this->events->publish(new ClientRegistered($client));

        $token = $this->tokens->issueTokenForClient($client->id());

        return [
            'client' => $this->presenter->present($client),
            'token' => $token,
        ];
    }
}

