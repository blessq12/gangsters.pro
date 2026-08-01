<?php

namespace App\Application\Client\useCases;

use App\Application\Client\DTO\LoginClientDto;
use App\Application\Client\Presenter\ClientPresenter;
use App\Domain\Client\Entity\Client;
use Illuminate\Auth\AuthenticationException;
use App\Domain\Client\Port\ClientAuthTokenPort;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

/**
 * Сценарий: вход клиента по телефону или email.
 */
final class LoginClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientAuthTokenPort $tokens,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(LoginClientDto $input): array
    {
        $client = $this->findClient($input);

        if (! Hash::check($input->password, $client->passwordHash())) {
            throw new AuthenticationException('Неверный телефон, email или пароль.');
        }

        $token = $this->tokens->issueToken($client->id());

        return $this->presenter->presentWithToken($client, $token);
    }

    private function findClient(LoginClientDto $input): Client
    {
        $phoneDigits = $input->phone !== null && $input->phone !== ''
            ? PhoneNumber::normalizeDigits($input->phone)
            : '';
        $email = $input->email !== null && trim($input->email) !== ''
            ? mb_strtolower(trim($input->email))
            : '';

        if ($phoneDigits !== '' && $email !== '') {
            throw new InvalidArgumentException('Укажи телефон или email, но не оба сразу.');
        }

        if ($phoneDigits !== '') {
            $client = $this->clients->findByPhone(PhoneNumber::fromRaw($phoneDigits));

            if ($client === null) {
                throw new AuthenticationException('Неверный телефон, email или пароль.');
            }

            return $client;
        }

        if ($email !== '') {
            $client = $this->clients->findByEmail($email);

            if ($client === null) {
                throw new AuthenticationException('Неверный телефон, email или пароль.');
            }

            return $client;
        }

        throw new InvalidArgumentException('Укажи телефон или email.');
    }
}
