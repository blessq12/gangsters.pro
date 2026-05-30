<?php

namespace App\Application\Operations\Client\Presenter;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;

final class AdminClientPresenter
{
    public function presentListItem(Client $client): array
    {
        return [
            'id' => $client->id(),
            'name' => $client->name(),
            'phone' => (string) $client->phone(),
            'email' => $client->email() ? (string) $client->email() : null,
            'status' => $client->status(),
            'created_at' => $client->createdAt()->format(DATE_ATOM),
        ];
    }

    public function presentDetail(Client $client): array
    {
        return [
            'id' => $client->id(),
            'name' => $client->name(),
            'phone' => (string) $client->phone(),
            'email' => $client->email() ? (string) $client->email() : null,
            'status' => $client->status(),
            'birth_date' => $client->birthDate()?->format('Y-m-d'),
            'consent_personal_data' => $client->consentPersonalData(),
            'consent_marketing' => $client->consentMarketing(),
            'addresses' => array_map(
                static fn (ClientAddress $address): array => [
                    'id' => $address->id(),
                    'type' => $address->type(),
                    'title' => $address->title(),
                    'street' => $address->street(),
                    'house' => $address->house(),
                    'entrance' => $address->entrance(),
                    'apartment' => $address->apartment(),
                ],
                $client->addresses(),
            ),
            'created_at' => $client->createdAt()->format(DATE_ATOM),
        ];
    }
}
