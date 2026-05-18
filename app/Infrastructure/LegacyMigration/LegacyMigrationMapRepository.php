<?php

namespace App\Infrastructure\LegacyMigration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyMigrationMapRepository
{
    public function tableExists(): bool
    {
        return Schema::hasTable('legacy_migration_maps');
    }

    public function findTargetKey(string $entityType, string $legacyKey): ?string
    {
        if (! $this->tableExists()) {
            return null;
        }

        $row = DB::table('legacy_migration_maps')
            ->where('entity_type', $entityType)
            ->where('legacy_key', $legacyKey)
            ->first();

        return $row !== null ? (string) $row->target_key : null;
    }

    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function remember(string $entityType, string $legacyKey, string $targetKey, ?array $meta = null): void
    {
        if (! $this->tableExists()) {
            return;
        }

        $now = now();
        $query = DB::table('legacy_migration_maps')
            ->where('entity_type', $entityType)
            ->where('legacy_key', $legacyKey);

        if ($query->exists()) {
            $query->update([
                'target_key' => $targetKey,
                'meta' => $meta !== null ? json_encode($meta, JSON_THROW_ON_ERROR) : null,
                'updated_at' => $now,
            ]);

            return;
        }

        DB::table('legacy_migration_maps')->insert([
            'entity_type' => $entityType,
            'legacy_key' => $legacyKey,
            'target_key' => $targetKey,
            'meta' => $meta !== null ? json_encode($meta, JSON_THROW_ON_ERROR) : null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function countByEntityType(string $entityType): int
    {
        if (! $this->tableExists()) {
            return 0;
        }

        return (int) DB::table('legacy_migration_maps')
            ->where('entity_type', $entityType)
            ->count();
    }
}
