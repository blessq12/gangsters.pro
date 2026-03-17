<?php

namespace App\Application\Client\Command;

use App\Application\Client\ClientBaseUseCase;
use App\Application\Client\DTO\UpdateClientDTO;
use DateTimeImmutable;
use LogicException;

final class UpdateClientUseCase extends ClientBaseUseCase
{
    public function execute(UpdateClientDTO $dto): array
    {
        $clientId = $this->authContext->currentClientId();

        $client = $this->clients->findById($clientId);

        if ($client === null) {
            throw new LogicException('Client not found');
        }

        if ($dto->phone !== null && (string) $client->phone() !== preg_replace('/\D+/', '', $dto->phone)) {
            if ($this->clients->existsByPhone($dto->phone)) {
                throw new LogicException('Client with this phone already exists');
            }

            $this->factory->changeContactsFromPrimitives($client, $dto->phone, null);
        }

        if ($dto->email !== null && ($client->email() === null || (string) $client->email() !== mb_strtolower(trim($dto->email)))) {
            if ($this->clients->existsByEmail($dto->email)) {
                throw new LogicException('Client with this email already exists');
            }

            $this->factory->changeContactsFromPrimitives($client, null, $dto->email);
        }

        if ($dto->name !== null) {
            $client->rename($dto->name);
        }

        if ($dto->birthDate !== null) {
            $client->changeBirthDate(new DateTimeImmutable($dto->birthDate));
        }

        if ($dto->consentPersonalData !== null || $dto->consentMarketing !== null) {
            $client->updateConsents(
                $dto->consentPersonalData ?? $client->consentPersonalData(),
                $dto->consentMarketing ?? $client->consentMarketing(),
            );
        }

        $this->clients->save($client);

        return [
            'client' => $this->presenter->present($client),
        ];
    }
}

