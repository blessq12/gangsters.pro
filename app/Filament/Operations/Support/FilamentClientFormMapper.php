<?php

namespace App\Filament\Operations\Support;

final class FilamentClientFormMapper
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function toFormState(array $payload): array
    {
        $client = $payload['client'] ?? [];

        return [
            'id' => $client['id'] ?? null,
            'name' => $client['name'] ?? '',
            'phone' => $client['phone'] ?? '',
            'email' => $client['email'] ?? '',
            'status' => $client['status'] ?? '',
            'birth_date' => $client['birth_date'] ?? '',
            'consent_personal_data' => (bool) ($client['consent_personal_data'] ?? false),
            'consent_marketing' => (bool) ($client['consent_marketing'] ?? false),
            'created_at' => $client['created_at'] ?? '',
            'addresses' => array_map(
                static fn (array $address): array => [
                    'type' => $address['type'] ?? '',
                    'title' => $address['title'] ?? '',
                    'street' => $address['street'] ?? '',
                    'house' => $address['house'] ?? '',
                    'entrance' => $address['entrance'] ?? '',
                    'apartment' => $address['apartment'] ?? '',
                ],
                $client['addresses'] ?? [],
            ),
            'orders' => $payload['orders']['items'] ?? [],
        ];
    }
}
