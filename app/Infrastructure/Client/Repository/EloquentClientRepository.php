<?php

namespace App\Infrastructure\Client\Repository;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\Repository\ClientRepository;
use App\Domain\Client\ValueObject\ClientAddressId;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\PhoneNumber;
use App\Infrastructure\Client\Mapper\ClientMapper;
use App\Infrastructure\Client\Model\CLN_Client;
use App\Infrastructure\Client\Model\CLN_ClientAddress;
use Illuminate\Support\Facades\DB;

final class EloquentClientRepository implements ClientRepository
{
    public function __construct(
        private readonly ClientMapper $mapper,
    ) {}

    public function findById(ClientId $id): ?Client
    {
        $row = CLN_Client::query()
            ->with('addresses')
            ->find($id->value());

        return $row instanceof CLN_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByPhone(PhoneNumber $phone): ?Client
    {
        $row = CLN_Client::query()
            ->with('addresses')
            ->where('phone', $phone->digits())
            ->first();

        return $row instanceof CLN_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByEmail(string $email): ?Client
    {
        $row = CLN_Client::query()
            ->with('addresses')
            ->where('email', mb_strtolower($email))
            ->first();

        return $row instanceof CLN_Client ? $this->mapper->toDomain($row) : null;
    }

    public function existsByPhone(PhoneNumber $phone): bool
    {
        return CLN_Client::query()
            ->where('phone', $phone->digits())
            ->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return CLN_Client::query()
            ->where('email', mb_strtolower($email))
            ->exists();
    }

    public function save(Client $client): void
    {
        DB::transaction(function () use ($client): void {
            $clientPayload = $this->mapper->toClientPersistence($client);

            if (! $client->hasId()) {
                unset($clientPayload['id']);
                $row = CLN_Client::query()->create($clientPayload);
                $client->assignId(ClientId::fromInt((int) $row->id));
            } else {
                CLN_Client::query()->updateOrCreate(
                    ['id' => $clientPayload['id']],
                    $clientPayload,
                );
            }

            $persistedAddressIds = [];

            foreach ($client->addresses() as $address) {
                $addressPayload = $this->mapper->toAddressPersistence(
                    $address,
                    $client->id()->value(),
                );

                if ($addressPayload['id'] !== null) {
                    CLN_ClientAddress::query()->updateOrCreate(
                        ['id' => $addressPayload['id']],
                        $addressPayload,
                    );
                    $persistedAddressIds[] = (int) $addressPayload['id'];

                    continue;
                }

                $row = CLN_ClientAddress::query()->create($addressPayload);
                $client->assignAddressId($address, ClientAddressId::fromInt((int) $row->id));
                $persistedAddressIds[] = (int) $row->id;
            }

            CLN_ClientAddress::query()
                ->where('client_id', $client->id()->value())
                ->whereNotIn('id', $persistedAddressIds)
                ->delete();
        });
    }
}
