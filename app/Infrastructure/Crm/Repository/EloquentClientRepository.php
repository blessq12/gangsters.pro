<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Infrastructure\Crm\Mapper\ClientMapper;
use App\Infrastructure\Crm\Model\CRM_Client;
use Illuminate\Support\Facades\DB;

final class EloquentClientRepository implements ClientRepository
{
    public function __construct(
        private readonly ClientMapper $mapper,
    ) {}

    public function save(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            $payload = $this->mapper->toPersistence($client);

            if (! $client->hasId()) {
                $row = CRM_Client::query()->create($payload);
                $client->assignId((int) $row->id);
                $this->mapper->syncChildren($row, $client);

                return;
            }

            CRM_Client::query()
                ->whereKey($client->id())
                ->update($payload);

            $row = CRM_Client::query()->find($client->id());
            if (! $row instanceof CRM_Client) {
                throw new \InvalidArgumentException('Клиент не найден.');
            }

            $this->mapper->syncChildren($row, $client);
        });
    }

    public function findById(int $id): ?Client
    {
        $row = CRM_Client::query()
            ->with(['addresses', 'favorites'])
            ->find($id);

        return $row instanceof CRM_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByPhone(string $phone): ?Client
    {
        $row = CRM_Client::query()
            ->with(['addresses', 'favorites'])
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
            ->with(['addresses', 'favorites'])
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

    public function count(): int
    {
        return CRM_Client::query()->count();
    }

    public function listPage(int $offset, int $limit): array
    {
        return CRM_Client::query()
            ->with(['addresses', 'favorites'])
            ->orderByDesc('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (CRM_Client $row): Client => $this->mapper->toDomain($row))
            ->all();
    }
}
