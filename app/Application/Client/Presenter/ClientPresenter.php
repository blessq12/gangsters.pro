<?php

namespace App\Application\Client\Presenter;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;

final class ClientPresenter
{
    public function present(Client $client): array
    {
        return [
            'id' => $client->id(),
            'name' => $client->name(),
            'phone' => (string) $client->phone(),
            'email' => $client->email() ? (string) $client->email() : null,
            'birth_date' => $client->birthDate()?->format('Y-m-d'),
            'status' => $client->status(),
            'consent_personal_data' => $client->consentPersonalData(),
            'consent_marketing' => $client->consentMarketing(),
            'default_address_id' => $client->defaultAddressId(),
            'addresses' => array_map(
                fn (ClientAddress $address) => $this->presentAddress($address),
                $client->addresses(),
            ),
            'created_at' => $client->createdAt()->format(DATE_ATOM),
            'updated_at' => $client->updatedAt()->format(DATE_ATOM),
        ];
    }

    private function presentAddress(ClientAddress $address): array
    {
        return [
            'id' => $address->id(),
            'client_id' => $address->clientId(),
            'type' => $address->type(),
            'title' => $address->title(),
            'street' => $address->street(),
            'house' => $address->house(),
            'liter' => $address->liter(),
            'staircase' => $address->staircase(),
            'apartment' => $address->apartment(),
            'entrance_code' => $address->entranceCode(),
            'floor' => $address->floor(),
            'comment' => $address->comment(),
            'created_at' => $address->createdAt()->format(DATE_ATOM),
            'updated_at' => $address->updatedAt()->format(DATE_ATOM),
        ];
    }
}

