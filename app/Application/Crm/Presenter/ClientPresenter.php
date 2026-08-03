<?php

namespace App\Application\Crm\Presenter;

use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Entity\ClientAddress;

final class ClientPresenter
{
    /**
     * @return array<string, mixed>
     */
    public function present(Client $client): array
    {
        $addresses = [];
        $defaultAddressId = null;

        foreach ($client->addresses() as $address) {
            $addresses[] = $this->presentAddress($address);
            if ($address->isDefault()) {
                $defaultAddressId = $address->id();
            }
        }

        return [
            'id' => $client->hasId() ? $client->id() : null,
            'name' => $client->name(),
            'phone' => $client->phone(),
            'email' => $client->email(),
            'birth_date' => $client->birthDate()?->format('Y-m-d'),
            'created_at' => $client->createdAt()->format(DATE_ATOM),
            'consent_personal_data' => $client->consentPersonalData(),
            'consent_marketing' => $client->consentMarketing(),
            'addresses' => $addresses,
            'default_address_id' => $defaultAddressId,
            'favorite_product_ids' => $client->favoriteProductIds(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentAddress(ClientAddress $address): array
    {
        return [
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
}
