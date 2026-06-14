<?php

namespace App\Infrastructure\Client\Mapper;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;
use App\Domain\Client\ValueObject\ClientAddressId;
use App\Domain\Client\ValueObject\ClientId;
use App\Domain\Client\ValueObject\PhoneNumber;
use App\Infrastructure\Client\Model\CLN_Client;
use App\Infrastructure\Client\Model\CLN_ClientAddress;
use DateTimeImmutable;

final class ClientMapper
{
    public function toDomain(CLN_Client $row): Client
    {
        $addresses = [];

        foreach ($row->addresses as $addressRow) {
            if ($addressRow instanceof CLN_ClientAddress) {
                $addresses[] = $this->mapAddress($addressRow);
            }
        }

        return Client::restore(
            id: ClientId::fromInt((int) $row->id),
            name: (string) $row->name,
            phone: PhoneNumber::fromRaw((string) $row->phone),
            email: (string) $row->email,
            birthDate: $row->birth_date !== null
                ? new DateTimeImmutable((string) $row->birth_date)
                : null,
            passwordHash: (string) $row->getRawOriginal('password'),
            consentPersonalData: (bool) $row->consent_personal_data,
            consentMarketing: (bool) $row->consent_marketing,
            addresses: $addresses,
            createdAt: new DateTimeImmutable((string) $row->created_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toClientPersistence(Client $client): array
    {
        return [
            'id' => $client->hasId() ? $client->id()->value() : null,
            'name' => $client->name(),
            'phone' => $client->phone()->digits(),
            'email' => $client->email(),
            'birth_date' => $client->birthDate()?->format('Y-m-d'),
            'password' => $client->passwordHash(),
            'consent_personal_data' => $client->consentPersonalData(),
            'consent_marketing' => $client->consentMarketing(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toAddressPersistence(ClientAddress $address, int $clientId): array
    {
        return [
            'id' => $address->id()?->value(),
            'client_id' => $clientId,
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

    private function mapAddress(CLN_ClientAddress $row): ClientAddress
    {
        return ClientAddress::restore(
            id: ClientAddressId::fromInt((int) $row->id),
            type: $row->type,
            title: $row->title,
            street: (string) $row->street,
            house: (string) $row->house,
            entrance: $row->entrance,
            apartment: $row->apartment,
            comment: $row->comment,
            isDefault: (bool) $row->is_default,
        );
    }
}
