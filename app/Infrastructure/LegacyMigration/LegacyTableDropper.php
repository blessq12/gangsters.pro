<?php

namespace App\Infrastructure\LegacyMigration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyTableDropper
{
    /**
     * @var list<string>
     */
    private const DROP_ORDER = [
        'order_items',
        'category_product',
        'product_images',
        'user_roles',
        'user_addresses',
        'orders',
        'products',
        'product_categories',
        'images',
        'roles',
        'yandex_food_modifiers_tables',
    ];

    /**
     * @return list<string>
     */
    public function legacyTableNames(): array
    {
        return self::DROP_ORDER;
    }

    /**
     * @return list<array{table: string, constraint: string, references: string}>
     */
    public function foreignKeysReferencingLegacyFromOutside(): array
    {
        $legacy = $this->legacyTableNames();
        $placeholders = implode(',', array_fill(0, count($legacy), '?'));

        $rows = DB::select(
            "SELECT TABLE_NAME as `table`, CONSTRAINT_NAME as `constraint`, REFERENCED_TABLE_NAME as references_table
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_NAME IN ({$placeholders})
               AND TABLE_NAME NOT IN ({$placeholders})",
            array_merge($legacy, $legacy),
        );

        $violations = [];
        foreach ($rows as $row) {
            $violations[] = [
                'table' => (string) $row->table,
                'constraint' => (string) $row->constraint,
                'references' => (string) $row->references_table,
            ];
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    public function dropAll(bool $dryRun = false): array
    {
        $dropped = [];

        foreach (self::DROP_ORDER as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            if (! $dryRun) {
                Schema::dropIfExists($table);
            }

            $dropped[] = $table;
        }

        return $dropped;
    }

    public function countLegacyOrders(): int
    {
        return Schema::hasTable('orders')
            ? (int) DB::table('orders')->count()
            : 0;
    }
}
