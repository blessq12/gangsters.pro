<?php

namespace App\Filament\Support;

use App\Shared\ValueObject\PhoneNumber;

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
                ? 'Клиент #'.$fallbackClientId
                : '—';
        }

        $name = trim((string) ($client['name'] ?? ''));
        $phone = trim((string) ($client['phone'] ?? ''));
        $clientId = isset($client['client_id']) ? (int) $client['client_id'] : $fallbackClientId;

        $phoneLabel = $phone !== '' ? self::formatPhone($phone) : '—';

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

        if ($digits === '') {
            return '—';
        }

        return PhoneNumber::tryFormatFromRaw($digits) ?? $digits;
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
