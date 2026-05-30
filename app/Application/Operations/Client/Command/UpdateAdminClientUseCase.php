<?php

namespace App\Application\Operations\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\DTO\UpdateAdminClientDTO;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Events\ClientProfileUpdated;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Events\DomainEventBus;
use DateTimeImmutable;

final class UpdateAdminClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFactory $factory,
        private readonly AdminClientPresenter $presenter,
        private readonly DomainEventBus $events,
    ) {
    }

    public function execute(UpdateAdminClientDTO $dto): array
    {
        $client = $this->clients->findById($dto->clientId);
        if ($client === null) {
            throw new ApiException('Client not found.', 404);
        }

        if ($dto->email !== null) {
            $normalized = mb_strtolower(trim($dto->email));
            $current = $client->email() ? (string) $client->email() : null;
            if ($current !== $normalized) {
                if ($this->clients->existsByEmail($normalized)) {
                    throw new ApiException('Client with this email already exists.', 422);
                }

                $this->factory->changeContactsFromPrimitives($client, null, $normalized);
            }
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
        $this->events->publish(new ClientProfileUpdated($client));

        return [
            'client' => $this->presenter->presentDetail($client),
        ];
    }
}
