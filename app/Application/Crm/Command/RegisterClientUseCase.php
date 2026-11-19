<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\DTO\RegisterClientDto;
use App\Application\Crm\Presenter\ClientPresenter;
use App\Application\Crm\Support\ClientAccessToken;
use App\Domain\Supporting\Customer\Entity\Client;
use App\Domain\Supporting\Customer\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use DateTimeImmutable;
use Illuminate\Support\Facades\Hash;

final class RegisterClientUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array{token: string, client: array<string, mixed>}
     */
    public function execute(RegisterClientDto $dto): array
    {
        $name = trim($dto->name);
        if ($name === '') {
            throw new \InvalidArgumentException('Имя обязательно.');
        }

        if (! $dto->consentPersonalData) {
            throw new \InvalidArgumentException('Согласие на ПДн обязательно.');
        }

        if (strlen($dto->password) < 6) {
            throw new \InvalidArgumentException('Пароль должен быть не короче 6 символов.');
        }

        $phone = PhoneNumber::formatFromRaw($dto->phone);
        if ($this->clients->existsByPhone($phone)) {
            throw new \InvalidArgumentException('Клиент с таким телефоном уже зарегистрирован.');
        }

        $email = $this->nullable($dto->email);
        if ($email !== null) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Некорректный email.');
            }
            if ($this->clients->findByEmail($email) !== null) {
                throw new \InvalidArgumentException('Клиент с таким email уже зарегистрирован.');
            }
        }

        $birthDate = null;
        if ($dto->birthDate !== null && trim($dto->birthDate) !== '') {
            $birthDate = new DateTimeImmutable(trim($dto->birthDate));
        }

        $client = Client::register(
            name: $name,
            phone: $phone,
            email: $email,
            birthDate: $birthDate,
            passwordHash: Hash::make($dto->password),
            consentPersonalData: true,
            consentMarketing: $dto->consentMarketing,
        );

        $this->clients->save($client);

        return [
            'token' => ClientAccessToken::issue($client->id()),
            'client' => $this->presenter->present($client),
        ];
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
