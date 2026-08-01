<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

final class UpdateClientProfileUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     * @return array{client: array<string, mixed>}
     */
    public function execute(int $clientId, array $input): array
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new AuthenticationException();
        }

        $name = array_key_exists('name', $input)
            ? (string) $input['name']
            : $client->name();

        $phone = $client->phone();
        if (array_key_exists('phone', $input)) {
            $phone = PhoneNumber::formatFromRaw((string) $input['phone']);
            $existing = $this->clients->findByPhone($phone);
            if ($existing !== null && $existing->id() !== $clientId) {
                throw ValidationException::withMessages([
                    'phone' => ['Этот телефон уже занят.'],
                ]);
            }
        }

        $email = array_key_exists('email', $input)
            ? ($input['email'] !== null ? (string) $input['email'] : null)
            : $client->email();

        $birthDate = $client->birthDate();
        if (array_key_exists('birth_date', $input)) {
            $birthDate = $input['birth_date']
                ? new DateTimeImmutable((string) $input['birth_date'])
                : null;
        }

        $consentMarketing = array_key_exists('consent_marketing', $input)
            ? (bool) $input['consent_marketing']
            : null;

        $client->updateProfile(
            name: $name,
            phone: $phone,
            email: $email,
            birthDate: $birthDate,
            consentMarketing: $consentMarketing,
        );

        $this->clients->save($client);

        return ['client' => $this->presenter->present($client)];
    }
}
