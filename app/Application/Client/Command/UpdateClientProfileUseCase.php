<?php

namespace App\Application\Client\Command;

use App\Application\Client\DTO\UpdateClientProfileDto;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Supporting\Client\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use DateTimeImmutable;

final class UpdateClientProfileUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $clientId, UpdateClientProfileDto $dto): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new \InvalidArgumentException('Клиент не найден.');
        }

        $name = $dto->name !== null ? trim($dto->name) : $client->name();
        $phoneRaw = $dto->phone !== null ? trim($dto->phone) : $client->phone();
        $phone = PhoneNumber::formatFromRaw($phoneRaw);

        if ($phone !== $client->phone() && $this->clients->existsByPhone($phone)) {
            throw new \InvalidArgumentException('Клиент с таким телефоном уже зарегистрирован.');
        }

        $email = $dto->email !== null ? $this->nullable($dto->email) : $client->email();
        if ($email !== null && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email.');
        }
        if ($email !== null && $email !== $client->email()) {
            $existing = $this->clients->findByEmail($email);
            if ($existing !== null && $existing->id() !== $clientId) {
                throw new \InvalidArgumentException('Клиент с таким email уже зарегистрирован.');
            }
        }

        $birthDate = $client->birthDate();
        if ($dto->birthDate !== null) {
            $birthDate = trim($dto->birthDate) === ''
                ? null
                : new DateTimeImmutable(trim($dto->birthDate));
        }

        $client->updateProfile(
            name: $name,
            phone: $phone,
            email: $email,
            birthDate: $birthDate,
            consentMarketing: $dto->consentMarketing,
        );

        $this->clients->save($client);

        return $this->presenter->present($client);
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
