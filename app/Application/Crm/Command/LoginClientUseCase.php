<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Port\ClientAccessTokenIssuer;
use App\Domain\Crm\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientAccessTokenIssuer $tokens,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @param  array{phone?: string|null, email?: string|null, password: string}  $input
     * @return array{token: string, client: array<string, mixed>}
     */
    public function execute(array $input): array
    {
        $password = (string) ($input['password'] ?? '');
        $phoneRaw = $input['phone'] ?? null;
        $emailRaw = $input['email'] ?? null;

        $client = null;
        if (is_string($phoneRaw) && trim($phoneRaw) !== '') {
            $phone = PhoneNumber::tryFormatFromRaw($phoneRaw);
            if ($phone === null) {
                throw ValidationException::withMessages([
                    'phone' => ['Некорректный телефон.'],
                ]);
            }
            $client = $this->clients->findByPhone($phone);
        } elseif (is_string($emailRaw) && trim($emailRaw) !== '') {
            $client = $this->clients->findByEmail(trim($emailRaw));
        } else {
            throw ValidationException::withMessages([
                'phone' => ['Укажите телефон или email.'],
            ]);
        }

        if (
            ! $client instanceof Client
            || ! Hash::check($password, $client->passwordHash())
        ) {
            throw ValidationException::withMessages([
                'password' => ['Неверный логин или пароль.'],
            ]);
        }

        return [
            'token' => $this->tokens->issue($client->id()),
            'client' => $this->presenter->present($client),
        ];
    }
}
