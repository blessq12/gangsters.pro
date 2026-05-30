<?php

namespace App\Application\Operations\Client\Command;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\DTO\CreateAdminClientDTO;
use App\Application\Operations\Client\Presenter\AdminClientPresenter;
use App\Domain\Client\Events\ClientRegistered;
use App\Domain\Client\Factory\ClientFactory;
use App\Domain\Client\Repository\ClientRepository;
use App\Shared\Events\DomainEventBus;
use Illuminate\Contracts\Hashing\Hasher;

final class CreateAdminClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientFactory $factory,
        private readonly Hasher $hasher,
        private readonly AdminClientPresenter $presenter,
        private readonly DomainEventBus $events,
    ) {}

    public function execute(CreateAdminClientDTO $dto): array
    {
        if ($this->clients->existsByPhone($dto->phone)) {
            throw new ApiException('Клиент с таким телефоном уже существует.', 422);
        }

        $email = filled($dto->email) ? mb_strtolower(trim($dto->email)) : null;

        if ($email !== null && $this->clients->existsByEmail($email)) {
            throw new ApiException('Клиент с таким email уже существует.', 422);
        }

        $client = $this->factory->createNew(
            name: $dto->name,
            phone: $dto->phone,
            email: $email,
            birthDate: $dto->birthDate,
            passwordHash: $dto->password !== null ? $this->hasher->make($dto->password) : null,
            consentPersonalData: $dto->consentPersonalData,
            consentMarketing: $dto->consentMarketing,
        );

        $this->clients->save($client);
        $this->events->publish(new ClientRegistered($client));

        return [
            'client' => $this->presenter->presentDetail($client),
        ];
    }
}
