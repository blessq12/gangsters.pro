<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Event\ClientCreated;
use App\Domain\Crm\Port\ClientAccessTokenIssuer;
use App\Domain\Crm\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class RegisterClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientAccessTokenIssuer $tokens,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     *     birth_date?: string|null,
     *     password: string,
     *     consent_personal_data: bool,
     *     consent_marketing?: bool
     * }  $input
     * @return array{token: string, client: array<string, mixed>}
     */
    public function execute(array $input): array
    {
        $phone = PhoneNumber::formatFromRaw($input['phone'] ?? null);

        if ($this->clients->existsByPhone($phone)) {
            throw ValidationException::withMessages([
                'phone' => ['Клиент с таким телефоном уже зарегистрирован.'],
            ]);
        }

        $password = (string) ($input['password'] ?? '');
        if (strlen($password) < 6) {
            throw ValidationException::withMessages([
                'password' => ['Пароль должен быть не короче 6 символов.'],
            ]);
        }

        $birthDate = null;
        if (! empty($input['birth_date'])) {
            $birthDate = new DateTimeImmutable((string) $input['birth_date']);
        }

        $client = Client::register(
            name: (string) ($input['name'] ?? ''),
            phone: $phone,
            email: isset($input['email']) ? (string) $input['email'] : null,
            birthDate: $birthDate,
            passwordHash: Hash::make($password),
            consentPersonalData: (bool) ($input['consent_personal_data'] ?? false),
            consentMarketing: (bool) ($input['consent_marketing'] ?? false),
        );

        $this->clients->save($client);

        Event::dispatch(ClientCreated::fromClient($client));

        return [
            'token' => $this->tokens->issue($client->id()),
            'client' => $this->presenter->present($client),
        ];
    }
}
