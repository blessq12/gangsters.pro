<?php

namespace App\Application\Crm\Command;

use App\Application\Crm\Presenter\AdminClientPresenter;
use App\Domain\Supporting\Customer\Entity\Client;
use App\Domain\Supporting\Customer\Repository\ClientRepository;
use App\Shared\ValueObject\PhoneNumber;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class EnsureClientByPhoneUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly AdminClientPresenter $presenter,
    ) {}

    /**
     * Найти клиента по телефону или создать запись в CRM.
     *
     * @return array{client: array<string, mixed>, created: bool}
     */
    public function execute(string $name, string $phone): array
    {
        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Имя обязательно.');
        }

        $canonicalPhone = PhoneNumber::formatFromRaw($phone);

        $existing = $this->clients->findByPhone($canonicalPhone);
        if ($existing instanceof Client) {
            return [
                'client' => $this->presenter->present($existing),
                'created' => false,
            ];
        }

        $client = Client::registerByStaff(
            name: $name,
            phone: $canonicalPhone,
            passwordHash: Hash::make(Str::password(32)),
        );

        try {
            $this->clients->save($client);
        } catch (QueryException $exception) {
            // Гонка по unique(phone): перечитываем.
            $again = $this->clients->findByPhone($canonicalPhone);
            if ($again instanceof Client) {
                return [
                    'client' => $this->presenter->present($again),
                    'created' => false,
                ];
            }

            throw $exception;
        }

        return [
            'client' => $this->presenter->present($client),
            'created' => true,
        ];
    }
}
