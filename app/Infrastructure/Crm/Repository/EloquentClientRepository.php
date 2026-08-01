<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Infrastructure\Crm\Mapper\ClientMapper;
use App\Infrastructure\Crm\Model\CRM_Client;

final class EloquentClientRepository implements ClientRepository
{
    public function __construct(
        private readonly ClientMapper $mapper,
    ) {}

    public function save(Client $client): void
    {
        $payload = $this->mapper->toPersistence($client);

        if (! $client->hasId()) {
            $row = CRM_Client::query()->create($payload);
            $client->assignId((int) $row->id);

            return;
        }

        CRM_Client::query()
            ->whereKey($client->id())
            ->update($payload);
    }

    public function findById(int $id): ?Client
    {
        $row = CRM_Client::query()->find($id);

        return $row instanceof CRM_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByPhone(string $phone): ?Client
    {
        $row = CRM_Client::query()
            ->where('phone', $phone)
            ->first();

        return $row instanceof CRM_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByEmail(string $email): ?Client
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        $row = CRM_Client::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();

        return $row instanceof CRM_Client ? $this->mapper->toDomain($row) : null;
    }

    public function existsByPhone(string $phone): bool
    {
        return CRM_Client::query()
            ->where('phone', $phone)
            ->exists();
    }
}
