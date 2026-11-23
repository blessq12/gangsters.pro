<?php

namespace App\Infrastructure\DataClone;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Фабрика подключения к исходной БД (Adapter к legacy-схеме).
 */
final class CloneSourceFactory
{
    public function make(
        string $database,
        string $username,
        string $password,
        string $host = '127.0.0.1',
        int $port = 3306,
    ): Connection {
        $name = 'data_clone_source';

        Config::set("database.connections.{$name}", [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge($name);

        return DB::connection($name);
    }
}
