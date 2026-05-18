<?php

namespace App\Infrastructure\LegacyMigration;

use App\Domain\Client\Entity\Client;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Client\Model\UR_ClientAddress;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyClientMigrator
{
    public function __construct(
        private readonly LegacyPhoneNormalizer $phoneNormalizer,
        private readonly LegacyMigrationMapRepository $maps,
    ) {}

    /**
     * @return array{created: int, updated: int, addresses: int, skipped: int}
     */
    public function migrate(bool $dryRun = false): array
    {
        if (! Schema::hasTable('orders') || ! Schema::hasTable('UR_clients')) {
            return ['created' => 0, 'updated' => 0, 'addresses' => 0, 'skipped' => 0];
        }

        $stats = ['created' => 0, 'updated' => 0, 'addresses' => 0, 'skipped' => 0];

        $this->migrateClientsFromOrderUsers($dryRun, $stats);
        $this->migrateClientsFromOrderPhones($dryRun, $stats);

        if (Schema::hasTable('user_addresses')) {
            $this->migrateUserAddresses($dryRun, $stats);
        }

        return $stats;
    }

    /**
     * @param  array{created: int, updated: int, addresses: int, skipped: int}  $stats
     */
    private function migrateClientsFromOrderUsers(bool $dryRun, array &$stats): void
    {
        $rows = DB::table('orders')
            ->selectRaw('user_id, MAX(tel) as tel, MAX(name) as name')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->get();

        foreach ($rows as $row) {
            $legacyUserId = (string) $row->user_id;
            $phone = $this->phoneNormalizer->normalize($row->tel);
            if ($phone === null) {
                $stats['skipped']++;

                continue;
            }

            $existingMap = $this->maps->findTargetKey(LegacyMigrationEntityType::LEGACY_USER, $legacyUserId);
            if ($existingMap !== null) {
                continue;
            }

            $clientId = $this->upsertClient(
                $phone,
                $this->clientName((string) ($row->name ?? '')),
                $dryRun,
                $stats,
            );

            if ($clientId === null) {
                $stats['skipped']++;

                continue;
            }

            if (! $dryRun) {
                $this->maps->remember(
                    LegacyMigrationEntityType::LEGACY_USER,
                    $legacyUserId,
                    (string) $clientId,
                );
            }
        }
    }

    /**
     * @param  array{created: int, updated: int, addresses: int, skipped: int}  $stats
     */
    private function migrateClientsFromOrderPhones(bool $dryRun, array &$stats): void
    {
        DB::table('orders')
            ->select(['tel', 'name'])
            ->whereNotNull('tel')
            ->where('tel', '!=', '')
            ->orderBy('id')
            ->chunk(500, function (Collection $chunk) use ($dryRun, &$stats): void {
                foreach ($chunk as $row) {
                    $phone = $this->phoneNormalizer->normalize($row->tel);
                    if ($phone === null) {
                        $stats['skipped']++;

                        continue;
                    }

                    if (UR_Client::query()->where('phone', $phone)->exists()) {
                        continue;
                    }

                    $this->upsertClient(
                        $phone,
                        $this->clientName((string) ($row->name ?? '')),
                        $dryRun,
                        $stats,
                    );
                }
            });
    }

    /**
     * @param  array{created: int, updated: int, addresses: int, skipped: int}  $stats
     */
    private function migrateUserAddresses(bool $dryRun, array &$stats): void
    {
        DB::table('user_addresses')
            ->orderBy('id')
            ->chunk(200, function (Collection $chunk) use ($dryRun, &$stats): void {
                foreach ($chunk as $address) {
                    $clientId = $this->resolveClientIdForLegacyUser((int) $address->user_id);
                    if ($clientId === null) {
                        $stats['skipped']++;

                        continue;
                    }

                    $exists = UR_ClientAddress::query()
                        ->where('client_id', $clientId)
                        ->where('street', (string) $address->street)
                        ->where('house', (string) $address->house)
                        ->where('apartment', (string) ($address->apartment ?? ''))
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    if ($dryRun) {
                        $stats['addresses']++;

                        continue;
                    }

                    UR_ClientAddress::query()->create([
                        'client_id' => $clientId,
                        'type' => 'additional',
                        'street' => (string) $address->street,
                        'house' => (string) $address->house,
                        'liter' => $address->building,
                        'staircase' => $address->staircase,
                        'floor' => $address->floor,
                        'apartment' => $address->apartment,
                    ]);

                    $stats['addresses']++;
                }
            });
    }

    private function resolveClientIdForLegacyUser(int $legacyUserId): ?int
    {
        $mapped = $this->maps->findTargetKey(
            LegacyMigrationEntityType::LEGACY_USER,
            (string) $legacyUserId,
        );
        if ($mapped !== null) {
            return (int) $mapped;
        }

        $orderRow = DB::table('orders')
            ->select(['tel'])
            ->where('user_id', $legacyUserId)
            ->whereNotNull('tel')
            ->orderByDesc('id')
            ->first();

        if ($orderRow === null) {
            return null;
        }

        $phone = $this->phoneNormalizer->normalize($orderRow->tel);
        if ($phone === null) {
            return null;
        }

        return UR_Client::query()->where('phone', $phone)->value('id');
    }

    /**
     * @param  array{created: int, updated: int, addresses: int, skipped: int}  $stats
     */
    private function upsertClient(string $phone, string $name, bool $dryRun, array &$stats): ?int
    {
        $existing = UR_Client::query()->where('phone', $phone)->first();

        if ($existing !== null) {
            $stats['updated']++;

            return (int) $existing->id;
        }

        if ($dryRun) {
            $stats['created']++;

            return 1;
        }

        $client = UR_Client::query()->create([
            'name' => $name,
            'phone' => $phone,
            'status' => Client::STATUS_ACTIVE,
            'consent_personal_data' => false,
            'consent_marketing' => false,
        ]);

        $stats['created']++;

        return (int) $client->id;
    }

    private function clientName(string $name): string
    {
        $trimmed = trim($name);

        return $trimmed !== '' ? $trimmed : 'Клиент';
    }
}
