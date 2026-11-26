<?php

namespace App\Infrastructure\Client\Mapper;

use App\Domain\Supporting\Client\Entity\Client;
use App\Domain\Supporting\Client\Entity\ClientAddress;
use App\Infrastructure\Client\Model\ClientAddressModel;
use App\Infrastructure\Client\Model\ClientFavoriteModel;
use App\Infrastructure\Client\Model\ClientModel;
use DateTimeImmutable;

final class ClientMapper
{
    public function toDomain(ClientModel $row): Client
    {
        $addresses = [];
        foreach ($row->addresses ?? [] as $addressRow) {
            if (! $addressRow instanceof ClientAddressModel) {
                continue;
            }

            $addresses[] = ClientAddress::restore(
                id: (string) $addressRow->id,
                type: null,
                title: null,
                street: (string) $addressRow->street,
                house: (string) $addressRow->house,
                entrance: $addressRow->entrance !== null ? (string) $addressRow->entrance : null,
                floor: $addressRow->floor !== null ? (string) $addressRow->floor : null,
                apartment: $addressRow->apartment !== null ? (string) $addressRow->apartment : null,
                comment: $addressRow->comment !== null ? (string) $addressRow->comment : null,
                isDefault: (bool) $addressRow->is_default,
                lat: $addressRow->lat !== null ? (float) $addressRow->lat : null,
                lng: $addressRow->lng !== null ? (float) $addressRow->lng : null,
            );
        }

        $favoriteProductIds = [];
        foreach ($row->favorites ?? [] as $favoriteRow) {
            if (! $favoriteRow instanceof ClientFavoriteModel) {
                continue;
            }

            $favoriteProductIds[] = (int) $favoriteRow->catalog_item_id;
        }

        return Client::restore(
            id: (int) $row->id,
            name: (string) $row->name,
            phone: (string) $row->phone,
            email: $row->email !== null ? (string) $row->email : null,
            birthDate: $row->birth_date !== null
                ? new DateTimeImmutable((string) $row->birth_date)
                : null,
            passwordHash: (string) $row->password_hash,
            consentPersonalData: (bool) $row->consent_personal_data,
            consentMarketing: (bool) $row->consent_marketing,
            addresses: $addresses,
            favoriteProductIds: $favoriteProductIds,
            createdAt: new DateTimeImmutable((string) $row->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(Client $client): array
    {
        return [
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
            'birth_date' => $client->birthDate()?->format('Y-m-d'),
            'password_hash' => $client->passwordHash(),
            'consent_personal_data' => $client->consentPersonalData(),
            'consent_marketing' => $client->consentMarketing(),
        ];
    }

    public function syncChildren(ClientModel $row, Client $client): void
    {
        ClientAddressModel::query()->where('client_id', $row->id)->delete();
        foreach ($client->addresses() as $address) {
            ClientAddressModel::query()->create([
                'client_id' => (int) $row->id,
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'floor' => $address->floor(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
                'lat' => $address->lat(),
                'lng' => $address->lng(),
                'is_default' => $address->isDefault(),
            ]);
        }

        ClientFavoriteModel::query()->where('client_id', $row->id)->delete();
        foreach ($client->favoriteProductIds() as $catalogItemId) {
            ClientFavoriteModel::query()->create([
                'client_id' => (int) $row->id,
                'catalog_item_id' => (int) $catalogItemId,
            ]);
        }
    }
}
