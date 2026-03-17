<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\RegisterDTO;
use LogicException;

final class RegisterClientUseCase extends ClientBaseUseCase
{
    public function execute(RegisterDTO $dto): array
    {
        if ($this->clients->existsByPhone($dto->phone)) {
            throw new LogicException('Client with this phone already exists');
        }

        if ($dto->email !== null && $this->clients->existsByEmail($dto->email)) {
            throw new LogicException('Client with this email already exists');
        }

        $client = $this->factory->createNew(
            name: $dto->name,
            phone: $dto->phone,
            email: $dto->email,
            birthDate: $dto->birthDate,
            rawPassword: $dto->password,
            consentPersonalData: $dto->consentPersonalData,
            consentMarketing: $dto->consentMarketing,
        );

        $this->clients->save($client);

        $token = $this->tokens->issueTokenForClient($client->id());

        return [
            'client' => $this->presenter->present($client),
            'token' => $token,
        ];
    }
}

