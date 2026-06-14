<?php

namespace App\Application\Client\Presenter;

use App\Domain\Client\Entity\Client;
use App\Domain\Client\Entity\ClientAddress;

final class ClientPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Client $client): array
    {
        return [
            'client' => [
                'id' => $client->id()->value(),
                'name' => $client->name(),
                'phone' => $client->phone()->digits(),
                'email' => $client->email(),
                'birth_date' => $client->birthDate()?->format('Y-m-d'),
                'consent_personal_data' => $client->consentPersonalData(),
                'consent_marketing' => $client->consentMarketing(),
                'addresses' => array_map(
                    fn (ClientAddress $address): array => $this->presentAddress($address),
                    $client->addresses(),
                ),
                'default_address_id' => $client->defaultAddressId(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function presentWithToken(Client $client, string $token): array
    {
        return [
            ...$this->present($client),
            'token' => $token,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentAddress(ClientAddress $address): array
    {
        return [
            'id' => $address->id()?->value(),
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
}
