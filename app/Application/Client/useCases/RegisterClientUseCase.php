<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\RegisterClientDto;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Event\ClientRegistered;
use App\Domain\Client\Exception\ClientAlreadyExistsException;
use App\Domain\Client\Port\ClientAuthTokenPort;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Сценарий: регистрация клиента и выдача токена.
 */
final class RegisterClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientAuthTokenPort $tokens,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(RegisterClientDto $input): array
    {
        $phone = PhoneNumber::fromRaw($input->phone);
        $email = $this->normalizeEmail($input->email);

        if ($this->clients->existsByPhone($phone)) {
            throw ClientAlreadyExistsException::byPhone();
        }

        if ($this->clients->existsByEmail($email)) {
            throw ClientAlreadyExistsException::byEmail();
        }

        $client = Client::register(
            name: trim($input->name),
            phone: $phone,
            email: $email,
            birthDate: $this->parseBirthDate($input->birthDate),
            passwordHash: Hash::make($input->password),
            consentPersonalData: $input->consentPersonalData,
            consentMarketing: $input->consentMarketing,
        );

        $this->clients->save($client);
        $client->markRegistered();

        foreach ($client->releaseEvents() as $event) {
            if ($event instanceof ClientRegistered) {
                Event::dispatch($event);
            }
        }

        $token = $this->tokens->issueToken($client->id());

        return $this->presenter->presentWithToken($client, $token);
    }

    private function normalizeEmail(string $email): string
    {
        $normalized = mb_strtolower(trim($email));

        if ($normalized === '') {
            throw new InvalidArgumentException('Email обязателен.');
        }

        return $normalized;
    }

    private function parseBirthDate(?string $birthDate): ?DateTimeImmutable
    {
        if ($birthDate === null || trim($birthDate) === '') {
            return null;
        }

        return new DateTimeImmutable($birthDate);
    }
}
