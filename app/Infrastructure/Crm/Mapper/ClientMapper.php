<?php

namespace App\Infrastructure\Crm\Mapper;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Entity\ClientAddress;
use App\Infrastructure\Crm\Model\CRM_Client;
use DateTimeImmutable;

final class ClientMapper
{
    public function toDomain(CRM_Client $row): Client
    {
        $addresses = [];

        foreach (($row->addresses ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = (string) ($item['id'] ?? '');
            if ($id === '') {
                continue;
            }

            $addresses[] = ClientAddress::restore(
                id: $id,
                type: isset($item['type']) ? (string) $item['type'] : null,
                title: isset($item['title']) ? (string) $item['title'] : null,
                street: (string) ($item['street'] ?? ''),
                house: (string) ($item['house'] ?? ''),
                entrance: isset($item['entrance']) ? (string) $item['entrance'] : null,
                apartment: isset($item['apartment']) ? (string) $item['apartment'] : null,
                comment: isset($item['comment']) ? (string) $item['comment'] : null,
                isDefault: (bool) ($item['is_default'] ?? false),
            );
        }

        $favoriteProductIds = [];
        foreach (($row->favorite_product_ids ?? []) as $productId) {
            $favoriteProductIds[] = (int) $productId;
        }

        return Client::restore(
            id: (int) $row->id,
            name: (string) $row->name,
            phone: (string) $row->phone,
            email: $row->email !== null ? (string) $row->email : null,
            birthDate: $row->birth_date !== null
                ? new DateTimeImmutable((string) $row->birth_date)
                : null,
            passwordHash: (string) $row->getRawOriginal('password'),
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
        $addresses = [];
        foreach ($client->addresses() as $address) {
            $addresses[] = [
                'id' => $address->id(),
                'type' => $address->type(),
                'title' => $address->title(),
                'street' => $address->street(),
                'house' => $address->house(),
                'entrance' => $address->entrance(),
                'apartment' => $address->apartment(),
                'comment' => $address->comment(),
                'is_default' => $address->isDefault(),
            ];
        }

        return [
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
            'birth_date' => $client->birthDate()?->format('Y-m-d'),
            'password' => $client->passwordHash(),
            'consent_personal_data' => $client->consentPersonalData(),
            'consent_marketing' => $client->consentMarketing(),
            'addresses' => $addresses,
            'favorite_product_ids' => $client->favoriteProductIds(),
        ];
    }
}
