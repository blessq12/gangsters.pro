<?php

namespace App\Filament\Resources\Orders\Concerns;

trait NormalizesOrderFormData
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeOrderFormData(array $data): array
    {
        $clientId = $data['client_id'] ?? null;
        if ($clientId === 0 || $clientId === '0' || $clientId === '') {
            $data['client_id'] = null;
        }

        $data['customer_address'] = $this->buildAddressArray(
            $data['customer_address_street'] ?? null,
            $data['customer_address_house'] ?? null,
            $data['customer_address_entrance'] ?? null,
            $data['customer_address_apartment'] ?? null,
        );

        $data['delivery_address'] = $this->buildAddressArray(
            $data['delivery_address_street'] ?? null,
            $data['delivery_address_house'] ?? null,
            $data['delivery_address_entrance'] ?? null,
            $data['delivery_address_apartment'] ?? null,
        );

        unset(
            $data['customer_address_street'],
            $data['customer_address_house'],
            $data['customer_address_entrance'],
            $data['customer_address_apartment'],
            $data['delivery_address_street'],
            $data['delivery_address_house'],
            $data['delivery_address_entrance'],
            $data['delivery_address_apartment'],
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function expandOrderFormDataForFill(array $data): array
    {
        $this->expandAddressIntoFields($data, 'customer_address', 'customer_address_');
        $this->expandAddressIntoFields($data, 'delivery_address', 'delivery_address_');

        if (($data['client_id'] ?? null) === null) {
            $data['client_id'] = 0;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function expandAddressIntoFields(array &$data, string $jsonKey, string $prefix): void
    {
        $addr = $data[$jsonKey] ?? null;
        if (! is_array($addr)) {
            return;
        }

        $data[$prefix.'street'] = $addr['street'] ?? null;
        $data[$prefix.'house'] = $addr['house'] ?? null;
        $data[$prefix.'entrance'] = $addr['entrance'] ?? null;
        $data[$prefix.'apartment'] = $addr['apartment'] ?? null;
    }

    /**
     * @return array<string, string>|null
     */
    private function buildAddressArray(
        ?string $street,
        ?string $house,
        ?string $entrance,
        ?string $apartment,
    ): ?array {
        $filtered = array_filter([
            'street' => $street,
            'house' => $house,
            'entrance' => $entrance,
            'apartment' => $apartment,
        ], fn ($v) => $v !== null && $v !== '');

        return $filtered === [] ? null : $filtered;
    }
}
