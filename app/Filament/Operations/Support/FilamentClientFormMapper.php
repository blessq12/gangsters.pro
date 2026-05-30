<?php

namespace App\Filament\Operations\Support;

use App\Application\Operations\Client\DTO\UpdateAdminClientDTO;

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
                    'id' => $address['id'] ?? null,
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

    /**
     * @param  array<string, mixed>  $data
     */
    public static function toUpdateDto(int $clientId, array $data): UpdateAdminClientDTO
    {
        return new UpdateAdminClientDTO(
            clientId: $clientId,
            name: filled($data['name'] ?? null) ? (string) $data['name'] : null,
            email: array_key_exists('email', $data) ? (string) ($data['email'] ?? '') : null,
            birthDate: filled($data['birth_date'] ?? null) ? (string) $data['birth_date'] : null,
            consentPersonalData: (bool) ($data['consent_personal_data'] ?? false),
            consentMarketing: (bool) ($data['consent_marketing'] ?? false),
        );
    }
}
