<?php

namespace App\Filament\Client\Support;

use App\Infrastructure\Client\Model\CLN_Client;
use App\Infrastructure\Client\Model\CLN_ClientAddress;

final class ClientProfileReader
{
    /**
     * @return array<string, mixed>
     */
    public static function formDataFromRecord(CLN_Client $record): array
    {
        $record->loadMissing('addresses');

        /** @var \Illuminate\Database\Eloquent\Collection<int, CLN_ClientAddress> $addresses */
        $addresses = $record->addresses;

        return [
            'id' => (string) $record->id,
            'name' => (string) $record->name,
            'phone' => self::formatPhone((string) $record->phone),
            'email' => (string) $record->email,
            'birth_date' => $record->birth_date?->format('d.m.Y') ?? '—',
            'created_at' => $record->created_at?->format('d.m.Y H:i') ?? '—',
            'addresses_count' => (string) $addresses->count(),
            'addresses' => self::mapAddresses($addresses->all()),
            'consent_personal_data' => self::boolLabel((bool) $record->consent_personal_data),
            'consent_marketing' => self::boolLabel((bool) $record->consent_marketing),
        ];
    }

    public static function formatPhone(string $digits): string
    {
        $digits = preg_replace('/\D+/', '', $digits) ?? '';

        if (strlen($digits) !== 10) {
            return $digits !== '' ? $digits : '—';
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 2),
            substr($digits, 8, 2),
        );
    }

    public static function boolLabel(bool $value): string
    {
        return $value ? 'Да' : 'Нет';
    }

    /**
     * @param  list<CLN_ClientAddress>  $addresses
     * @return list<array<string, string>>
     */
    private static function mapAddresses(array $addresses): array
    {
        usort(
            $addresses,
            static function (CLN_ClientAddress $left, CLN_ClientAddress $right): int {
                $defaultOrder = (int) $right->is_default <=> (int) $left->is_default;

                if ($defaultOrder !== 0) {
                    return $defaultOrder;
                }

                return $left->id <=> $right->id;
            },
        );

        $mapped = [];

        foreach ($addresses as $address) {
            $mapped[] = [
                'id' => (string) $address->id,
                'title' => self::nullableText($address->title),
                'street' => (string) $address->street,
                'house' => (string) $address->house,
                'entrance' => self::nullableText($address->entrance),
                'apartment' => self::nullableText($address->apartment),
                'comment' => self::nullableText($address->comment),
                'is_default' => self::boolLabel((bool) $address->is_default),
            ];
        }

        return $mapped;
    }

    private static function nullableText(?string $value): string
    {
        $text = trim((string) $value);

        return $text !== '' ? $text : '—';
    }
}
