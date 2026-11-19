<?php

namespace App\Infrastructure\Crm\Mapper;

use App\Domain\Supporting\Customer\Entity\Client;
use App\Domain\Supporting\Customer\Entity\ClientAddress;
use App\Infrastructure\Crm\Model\CRM_Client;
use App\Infrastructure\Crm\Model\CRM_ClientAddress;
use App\Infrastructure\Crm\Model\CRM_ClientFavorite;
use DateTimeImmutable;

final class ClientMapper
{
    public function toDomain(CRM_Client $row): Client
    {
        $addresses = [];
        foreach ($row->addresses ?? [] as $addressRow) {
            if (! $addressRow instanceof CRM_ClientAddress) {
                continue;
            }

            $addresses[] = ClientAddress::restore(
                id: (string) $addressRow->address_key,
                type: $addressRow->type !== null ? (string) $addressRow->type : null,
                title: $addressRow->title !== null ? (string) $addressRow->title : null,
                street: (string) $addressRow->street,
                house: (string) $addressRow->house,
                entrance: $addressRow->entrance !== null ? (string) $addressRow->entrance : null,
                floor: $addressRow->floor !== null ? (string) $addressRow->floor : null,
                apartment: $addressRow->apartment !== null ? (string) $addressRow->apartment : null,
                comment: $addressRow->comment !== null ? (string) $addressRow->comment : null,
                isDefault: (bool) $addressRow->is_default,
            );
        }

        $favoriteProductIds = [];
        foreach ($row->favorites ?? [] as $favoriteRow) {
            if (! $favoriteRow instanceof CRM_ClientFavorite) {
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

    public function syncChildren(CRM_Client $row, Client $client): void
    {
        CRM_ClientAddress::query()->where('client_id', $row->id)->delete();
        foreach ($client->addresses() as $address) {
            CRM_ClientAddress::query()->create([
                'client_id' => (int) $row->id,
                'address_key' => $address->id(),
                'type' => $address->type(),
                'title' => $address->title(),
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'floor' => $address->floor(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
                'is_default' => $address->isDefault(),
            ]);
        }

        CRM_ClientFavorite::query()->where('client_id', $row->id)->delete();
        foreach ($client->favoriteProductIds() as $catalogItemId) {
            CRM_ClientFavorite::query()->create([
                'client_id' => (int) $row->id,
                'catalog_item_id' => (int) $catalogItemId,
            ]);
        }
    }
}
