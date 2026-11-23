<?php

namespace App\Infrastructure\Client\Repository;

use App\Domain\Supporting\Client\Entity\Client;
use App\Domain\Supporting\Client\Repository\ClientRepository;
use App\Infrastructure\Client\Mapper\ClientMapper;
use App\Infrastructure\Client\Model\ClientModel;
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
                $row = ClientModel::query()->create($payload);
                $client->assignId((int) $row->id);
                $this->mapper->syncChildren($row, $client);

                return;
            }

            ClientModel::query()
                ->whereKey($client->id())
                ->update($payload);

            $row = ClientModel::query()->find($client->id());
            if (! $row instanceof ClientModel) {
                throw new \InvalidArgumentException('Клиент не найден.');
            }

            $this->mapper->syncChildren($row, $client);
        });
    }

    public function findById(int $id): ?Client
    {
        $row = ClientModel::query()
            ->with(['addresses', 'favorites'])
            ->find($id);

        return $row instanceof ClientModel ? $this->mapper->toDomain($row) : null;
    }

    public function findByPhone(string $phone): ?Client
    {
        $row = ClientModel::query()
            ->with(['addresses', 'favorites'])
            ->where('phone', $phone)
            ->first();

        return $row instanceof ClientModel ? $this->mapper->toDomain($row) : null;
    }

    public function findByEmail(string $email): ?Client
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        $row = ClientModel::query()
            ->with(['addresses', 'favorites'])
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();

        return $row instanceof ClientModel ? $this->mapper->toDomain($row) : null;
    }

    public function existsByPhone(string $phone): bool
    {
        return ClientModel::query()
            ->where('phone', $phone)
            ->exists();
    }

    public function count(): int
    {
        return ClientModel::query()->count();
    }

    public function listPage(int $offset, int $limit): array
    {
        return ClientModel::query()
            ->with(['addresses', 'favorites'])
            ->orderByDesc('id')
            ->offset(max(0, $offset))
            ->limit(max(1, $limit))
            ->get()
            ->map(fn (ClientModel $row): Client => $this->mapper->toDomain($row))
            ->all();
    }
}
