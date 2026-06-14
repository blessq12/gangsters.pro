<?php

namespace App\Filament\Support;

use App\Filament\Client\Support\ClientProfileReader;
use App\Infrastructure\Client\Model\CLN_Client;

final class ClientSnapshotLabel
{
    /**
     * Подпись клиента для таблиц Filament (оформления, заказы).
     */
    public static function forList(mixed $snapshot, ?int $fallbackClientId = null): string
    {
        $client = self::normalizeSnapshot($snapshot);

        if ($client === []) {
            return $fallbackClientId !== null
                ? self::fromClientId($fallbackClientId)
                : '—';
        }

        $name = trim((string) ($client['name'] ?? ''));
        $phone = trim((string) ($client['phone'] ?? ''));
        $clientId = isset($client['client_id']) ? (int) $client['client_id'] : $fallbackClientId;

        if ($name === '' && $clientId !== null) {
            $resolved = self::fromClientId($clientId);
            if ($resolved !== '—') {
                return $resolved;
            }
        }

        $phoneLabel = $phone !== '' ? ClientProfileReader::formatPhone($phone) : '—';

        if ($name !== '' && $phoneLabel !== '—') {
            return $name.' · '.$phoneLabel;
        }

        if ($name !== '') {
            return $name;
        }

        if ($phoneLabel !== '—') {
            return $phoneLabel;
        }

        if (($client['kind'] ?? '') === 'registered' && $clientId !== null) {
            return 'Клиент #'.$clientId;
        }

        if (($client['kind'] ?? '') === 'guest') {
            return 'Гость';
        }

        return '—';
    }

    public static function formatPhone(?string $phone): string
    {
        $digits = trim((string) $phone);

        return $digits !== '' ? ClientProfileReader::formatPhone($digits) : '—';
    }

    private static function fromClientId(int $clientId): string
    {
        $row = CLN_Client::query()->find($clientId);

        if ($row === null) {
            return 'Клиент #'.$clientId;
        }

        $name = trim((string) $row->name);
        $phone = ClientProfileReader::formatPhone((string) $row->phone);

        if ($name !== '' && $phone !== '—') {
            return $name.' · '.$phone;
        }

        if ($name !== '') {
            return $name;
        }

        return $phone !== '—' ? $phone : 'Клиент #'.$clientId;
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeSnapshot(mixed $snapshot): array
    {
        if (is_array($snapshot)) {
            return $snapshot;
        }

        if (! is_string($snapshot) || trim($snapshot) === '') {
            return [];
        }

        $decoded = json_decode($snapshot, true);

        return is_array($decoded) ? $decoded : [];
    }
}
