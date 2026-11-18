<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\LoginClientDto;
use App\Application\Crm\Presenter\ClientPresenter;
use App\Application\Crm\Support\ClientAccessToken;
use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use Illuminate\Support\Facades\Hash;

final class LoginClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array{token: string, client: array<string, mixed>}
     */
    public function execute(LoginClientDto $dto): array
    {
        $client = $this->resolveClient($dto);
        if (! $client instanceof Client) {
            throw new \InvalidArgumentException('Неверный логин или пароль.');
        }

        if (! Hash::check($dto->password, $client->passwordHash())) {
            throw new \InvalidArgumentException('Неверный логин или пароль.');
        }

        return [
            'token' => ClientAccessToken::issue($client->id()),
            'client' => $this->presenter->present($client),
        ];
    }

    private function resolveClient(LoginClientDto $dto): ?Client
    {
        $phone = $this->nullable($dto->phone);
        if ($phone !== null) {
            try {
                return $this->clients->findByPhone(PhoneNumber::formatFromRaw($phone));
            } catch (\InvalidArgumentException) {
                return null;
            }
        }

        $email = $this->nullable($dto->email);
        if ($email !== null) {
            return $this->clients->findByEmail($email);
        }

        throw new \InvalidArgumentException('Нужен phone или email.');
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
