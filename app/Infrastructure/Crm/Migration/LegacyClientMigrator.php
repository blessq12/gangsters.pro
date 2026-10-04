<?php

namespace App\Infrastructure\Crm\Migration;

use App\Infrastructure\Crm\Model\CRM_Client;
use App\Shared\ValueObject\PhoneNumber;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

/**
 * Adapter: читает users/user_addresses/personal_access_tokens из legacy-БД
 * и пишет CRM_clients + remorph-токены в текущую БД.
 */
final class LegacyClientMigrator
{
    private const LEGACY_TOKENABLE = 'App\\Models\\User';

    private const TARGET_TOKENABLE = CRM_Client::class;

    /**
     * @return array{report: LegacyClientMigrationReport, idMap: array<int, int>}
     */
    public function migrate(
        string $legacyConnection,
        bool $dryRun = false,
        bool $preserveIds = true,
        int $chunk = 200,
    ): array {
        $this->assertLegacyTables($legacyConnection);
        $this->assertTargetTables();

        $report = new LegacyClientMigrationReport();
        /** @var array<int, int> $idMap legacy user id → CRM_clients id */
        $idMap = [];

        DB::connection($legacyConnection)
            ->table('users')
            ->orderBy('id')
            ->chunkById($chunk, function ($users) use (
                $legacyConnection,
                $dryRun,
                $preserveIds,
                $report,
                &$idMap,
            ): void {
                foreach ($users as $user) {
                    $report->bumpClientsRead();
                    $legacyId = (int) $user->id;

                    $result = $this->migrateClient(
                        user: $user,
                        legacyConnection: $legacyConnection,
                        dryRun: $dryRun,
                        preserveIds: $preserveIds,
                        report: $report,
                    );

                    if ($result !== null) {
                        $idMap[$legacyId] = $result;
                    }
                }
            });

        $this->migrateTokens(
            legacyConnection: $legacyConnection,
            idMap: $idMap,
            dryRun: $dryRun,
            chunk: $chunk,
            report: $report,
        );

        if (! $dryRun && $preserveIds && $idMap !== []) {
            $this->bumpAutoIncrement(max($idMap));
        }

        return [
            'report' => $report,
            'idMap' => $idMap,
        ];
    }

    private function assertLegacyTables(string $legacyConnection): void
    {
        $schema = Schema::connection($legacyConnection);

        foreach (['users', 'personal_access_tokens'] as $table) {
            if (! $schema->hasTable($table)) {
                throw new \RuntimeException("В legacy-БД нет таблицы {$table}.");
            }
        }
    }

    private function assertTargetTables(): void
    {
        foreach (['CRM_clients', 'personal_access_tokens'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException("В целевой БД нет таблицы {$table}.");
            }
        }
    }

    private function migrateClient(
        stdClass $user,
        string $legacyConnection,
        bool $dryRun,
        bool $preserveIds,
        LegacyClientMigrationReport $report,
    ): ?int {
        $legacyId = (int) $user->id;
        $phone = PhoneNumber::tryFormatFromRaw($user->tel ?? null);

        if ($phone === null) {
            $report->bumpClientsSkipped();
            $report->warn("user#{$legacyId}: пропуск — нет валидного телефона (tel=".json_encode($user->tel ?? null).').');

            return null;
        }

        $existingByPhone = DB::table('CRM_clients')->where('phone', $phone)->first();
        if ($existingByPhone !== null) {
            $existingId = (int) $existingByPhone->id;
            $report->bumpClientsSkipped();
            $report->warn("user#{$legacyId}: пропуск — phone уже есть у CRM_clients#{$existingId}.");

            // Если телефон уже замаплен на того же id — токены всё равно можно перенести.
            if ($preserveIds && $existingId === $legacyId) {
                return $existingId;
            }

            if (! $preserveIds) {
                return $existingId;
            }

            return null;
        }

        if ($preserveIds) {
            $existingById = DB::table('CRM_clients')->where('id', $legacyId)->first();
            if ($existingById !== null) {
                $report->bumpClientsSkipped();
                $report->warn("user#{$legacyId}: пропуск — id занят другим клиентом (phone={$existingById->phone}).");

                return null;
            }
        }

        $addresses = $this->loadAddresses($legacyConnection, $legacyId);
        $payload = [
            'name' => (string) ($user->name ?? ''),
            'phone' => $phone,
            'email' => $this->nullableString($user->email ?? null),
            'birth_date' => $this->formatBirthDate($user->dob ?? null),
            'password' => (string) ($user->password ?? ''),
            'consent_personal_data' => true,
            'consent_marketing' => false,
            'addresses' => json_encode($addresses, JSON_UNESCAPED_UNICODE),
            'favorite_product_ids' => json_encode([]),
            'created_at' => $user->created_at ?? now(),
            'updated_at' => $user->updated_at ?? now(),
        ];

        if ($payload['name'] === '' || $payload['password'] === '') {
            $report->bumpClientsSkipped();
            $report->warn("user#{$legacyId}: пропуск — пустые name/password.");

            return null;
        }

        if ($dryRun) {
            $report->bumpClientsInserted();

            return $preserveIds ? $legacyId : $legacyId;
        }

        if ($preserveIds) {
            $payload['id'] = $legacyId;
            DB::table('CRM_clients')->insert($payload);
            $report->bumpClientsInserted();

            return $legacyId;
        }

        $newId = (int) DB::table('CRM_clients')->insertGetId($payload);
        $report->bumpClientsInserted();

        return $newId;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadAddresses(string $legacyConnection, int $userId): array
    {
        if (! Schema::connection($legacyConnection)->hasTable('user_addresses')) {
            return [];
        }

        $rows = DB::connection($legacyConnection)
            ->table('user_addresses')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        $addresses = [];
        $index = 0;

        foreach ($rows as $row) {
            $street = trim((string) ($row->street ?? ''));
            $house = trim((string) ($row->house ?? ''));
            if ($street === '' || $house === '') {
                continue;
            }

            $commentParts = [];
            $building = trim((string) ($row->building ?? ''));
            if ($building !== '') {
                $commentParts[] = 'корп. '.$building;
            }

            $addresses[] = [
                'id' => bin2hex(random_bytes(8)),
                'type' => null,
                'title' => null,
                'street' => $street,
                'house' => $house,
                'entrance' => $this->nullableString($row->staircase ?? null),
                'floor' => $this->nullableString($row->floor ?? null),
                'apartment' => $this->nullableString($row->apartment ?? null),
                'comment' => $commentParts === [] ? null : implode(', ', $commentParts),
                'is_default' => $index === 0,
            ];
            $index++;
        }

        return $addresses;
    }

    /**
     * @param  array<int, int>  $idMap
     */
    private function migrateTokens(
        string $legacyConnection,
        array $idMap,
        bool $dryRun,
        int $chunk,
        LegacyClientMigrationReport $report,
    ): void {
        if ($idMap === []) {
            return;
        }

        $legacyIds = array_keys($idMap);

        DB::connection($legacyConnection)
            ->table('personal_access_tokens')
            ->where('tokenable_type', self::LEGACY_TOKENABLE)
            ->whereIn('tokenable_id', $legacyIds)
            ->orderBy('id')
            ->chunkById($chunk, function ($tokens) use ($idMap, $dryRun, $report): void {
                foreach ($tokens as $token) {
                    $report->bumpTokensRead();
                    $legacyUserId = (int) $token->tokenable_id;
                    $clientId = $idMap[$legacyUserId] ?? null;

                    if ($clientId === null) {
                        $report->bumpTokensSkipped();
                        $report->warn("token#{$token->id}: пропуск — нет map для user#{$legacyUserId}.");

                        continue;
                    }

                    $hash = (string) ($token->token ?? '');
                    if ($hash === '') {
                        $report->bumpTokensSkipped();
                        $report->warn("token#{$token->id}: пропуск — пустой hash.");

                        continue;
                    }

                    $exists = DB::table('personal_access_tokens')
                        ->where('token', $hash)
                        ->exists();

                    if ($exists) {
                        $report->bumpTokensSkipped();
                        $report->warn("token#{$token->id}: пропуск — hash уже есть в целевой БД.");

                        continue;
                    }

                    if ($dryRun) {
                        $report->bumpTokensInserted();

                        continue;
                    }

                    DB::table('personal_access_tokens')->insert([
                        'tokenable_type' => self::TARGET_TOKENABLE,
                        'tokenable_id' => $clientId,
                        'name' => (string) ($token->name ?? 'gangsta'),
                        'token' => $hash,
                        'abilities' => $token->abilities,
                        'last_used_at' => $token->last_used_at,
                        'expires_at' => $token->expires_at ?? null,
                        'created_at' => $token->created_at ?? now(),
                        'updated_at' => $token->updated_at ?? now(),
                    ]);
                    $report->bumpTokensInserted();
                }
            });
    }

    private function bumpAutoIncrement(int $maxId): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        $next = $maxId + 1;
        DB::statement("ALTER TABLE CRM_clients AUTO_INCREMENT = {$next}");
    }

    private function formatBirthDate(mixed $dob): ?string
    {
        if ($dob === null || $dob === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $dob)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
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
