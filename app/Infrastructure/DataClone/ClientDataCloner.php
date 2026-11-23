<?php

namespace App\Infrastructure\DataClone;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * CRM_clients → CLI_clients + адреса + избранное.
 */
final class ClientDataCloner
{
    /**
     * @return array{clients: int, addresses: int, favorites: int, skipped_favorites: int}
     */
    public function clone(Connection $source): array
    {
        $clients = $source->table('CRM_clients')->orderBy('id')->get();
        $catalogIds = DB::table('CAT_items')->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->flip()
            ->all();

        $stats = [
            'clients' => 0,
            'addresses' => 0,
            'favorites' => 0,
            'skipped_favorites' => 0,
        ];

        DB::transaction(function () use ($clients, $catalogIds, &$stats): void {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('CLI_client_favorites')->delete();
            DB::table('CLI_client_addresses')->delete();
            DB::table('CLI_clients')->delete();

            foreach ($clients as $client) {
                $clientId = (int) $client->id;

                DB::table('CLI_clients')->insert([
                    'id' => $clientId,
                    'name' => (string) $client->name,
                    'phone' => (string) $client->phone,
                    'email' => $client->email,
                    'birth_date' => $client->birth_date,
                    'password_hash' => (string) $client->password,
                    'consent_personal_data' => (bool) $client->consent_personal_data,
                    'consent_marketing' => (bool) $client->consent_marketing,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
                ]);
                $stats['clients']++;

                foreach ($this->decodeList($client->addresses) as $address) {
                    if (! is_array($address)) {
                        continue;
                    }

                    $street = trim((string) ($address['street'] ?? ''));
                    $house = trim((string) ($address['house'] ?? ''));
                    if ($street === '' || $house === '') {
                        continue;
                    }

                    DB::table('CLI_client_addresses')->insert([
                        'client_id' => $clientId,
                        'street' => $street,
                        'house' => $house,
                        'apartment' => $this->nullableString($address['apartment'] ?? null),
                        'entrance' => $this->nullableString($address['entrance'] ?? null),
                        'floor' => $this->nullableString($address['floor'] ?? null),
                        'comment' => $this->nullableString($address['comment'] ?? null),
                        'lat' => isset($address['lat']) ? (float) $address['lat'] : null,
                        'lng' => isset($address['lng']) ? (float) $address['lng'] : null,
                        'is_default' => (bool) ($address['is_default'] ?? false),
                        'created_at' => $client->created_at,
                        'updated_at' => $client->updated_at,
                    ]);
                    $stats['addresses']++;
                }

                $seenFavorites = [];
                foreach ($this->decodeList($client->favorite_product_ids) as $productId) {
                    $catalogItemId = (int) $productId;
                    if ($catalogItemId <= 0 || isset($seenFavorites[$catalogItemId])) {
                        continue;
                    }
                    $seenFavorites[$catalogItemId] = true;

                    if (! isset($catalogIds[$catalogItemId])) {
                        $stats['skipped_favorites']++;

                        continue;
                    }

                    DB::table('CLI_client_favorites')->insert([
                        'client_id' => $clientId,
                        'catalog_item_id' => $catalogItemId,
                        'created_at' => $client->created_at,
                        'updated_at' => $client->updated_at,
                    ]);
                    $stats['favorites']++;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return $stats;
    }

    /**
     * @return list<mixed>
     */
    private function decodeList(mixed $raw): array
    {
        if ($raw === null || $raw === '' || $raw === 'null') {
            return [];
        }

        if (is_array($raw)) {
            return array_values($raw);
        }

        if (! is_string($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
