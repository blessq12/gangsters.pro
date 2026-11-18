<?php

namespace App\Infrastructure\Storage\Migration\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;

final class ConnectionSchema
{
    public static function hasTable(ConnectionInterface $connection, string $table): bool
    {
        return Schema::connection($connection->getName())->hasTable($table);
    }

    public static function hasColumn(ConnectionInterface $connection, string $table, string $column): bool
    {
        return Schema::connection($connection->getName())->hasColumn($table, $column);
    }

    /**
     * @param  list<string>  $tables
     */
    public static function truncate(ConnectionInterface $connection, array $tables): void
    {
        $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($tables as $table) {
                if (self::hasTable($connection, $table)) {
                    $connection->table($table)->truncate();
                }
            }
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public static function decodeJson(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
