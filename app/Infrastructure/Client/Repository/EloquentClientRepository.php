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
use App\Infrastructure\Client\Model\CLN_ClientFavorite;
use Illuminate\Support\Facades\DB;

final class EloquentClientRepository implements ClientRepository
{
    public function __construct(
        private readonly ClientMapper $mapper,
    ) {}

    public function findById(ClientId $id): ?Client
    {
        $row = CLN_Client::query()
            ->with(['addresses', 'favorites'])
            ->find($id->value());

        return $row instanceof CLN_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByPhone(PhoneNumber $phone): ?Client
    {
        $row = CLN_Client::query()
            ->with(['addresses', 'favorites'])
            ->where('phone', $phone->formatted())
            ->first();

        return $row instanceof CLN_Client ? $this->mapper->toDomain($row) : null;
    }

    public function findByEmail(string $email): ?Client
    {
        $row = CLN_Client::query()
            ->with(['addresses', 'favorites'])
            ->where('email', mb_strtolower($email))
            ->first();

        return $row instanceof CLN_Client ? $this->mapper->toDomain($row) : null;
    }

    public function existsByPhone(PhoneNumber $phone): bool
    {
        return CLN_Client::query()
            ->where('phone', $phone->formatted())
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

            $persistedFavoriteProductIds = [];

            foreach ($client->favorites() as $favorite) {
                $favoritePayload = $this->mapper->toFavoritePersistence(
                    $favorite,
                    $client->id()->value(),
                );

                CLN_ClientFavorite::query()->updateOrCreate(
                    [
                        'client_id' => $favoritePayload['client_id'],
                        'product_id' => $favoritePayload['product_id'],
                    ],
                    $favoritePayload,
                );

                $persistedFavoriteProductIds[] = (int) $favoritePayload['product_id'];
            }

            $favoriteQuery = CLN_ClientFavorite::query()
                ->where('client_id', $client->id()->value());

            if ($persistedFavoriteProductIds !== []) {
                $favoriteQuery->whereNotIn('product_id', $persistedFavoriteProductIds);
            }

            $favoriteQuery->delete();
        });
    }
}
