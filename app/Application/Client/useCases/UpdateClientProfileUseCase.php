<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\UpdateClientProfileDto;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Exception\ClientAlreadyExistsException;
use App\Domain\Client\Exception\ClientNotFoundException;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\PhoneNumber;
use DateTimeImmutable;

/**
 * Сценарий: обновить профиль клиента.
 */
final class UpdateClientProfileUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(UpdateClientProfileDto $input): array
    {
        $client = $this->findClient($input->clientId);
        $phone = PhoneNumber::fromRaw($input->phone);
        $email = $input->email !== null && trim($input->email) !== ''
            ? mb_strtolower(trim($input->email))
            : null;

        $existingByPhone = $this->clients->findByPhone($phone);
        if ($existingByPhone !== null && $existingByPhone->id()->value() !== $client->id()->value()) {
            throw ClientAlreadyExistsException::byPhone();
        }

        if ($email !== null) {
            $existingByEmail = $this->clients->findByEmail($email);
            if ($existingByEmail !== null && $existingByEmail->id()->value() !== $client->id()->value()) {
                throw ClientAlreadyExistsException::byEmail();
            }
        }

        $client->updateProfile(
            name: trim($input->name),
            phone: $phone,
            email: $email,
            birthDate: $this->parseBirthDate($input->birthDate),
            consentPersonalData: $input->consentPersonalData,
            consentMarketing: $input->consentMarketing,
        );

        $this->clients->save($client);

        return $this->presenter->present($client);
    }

    private function findClient(int $clientId): Client
    {
        $client = $this->clients->findById(ClientId::fromInt($clientId));

        if ($client === null) {
            throw ClientNotFoundException::forId($clientId);
        }

        return $client;
    }

    private function parseBirthDate(?string $birthDate): ?DateTimeImmutable
    {
        if ($birthDate === null || trim($birthDate) === '') {
            return null;
        }

        return new DateTimeImmutable($birthDate);
    }
}
