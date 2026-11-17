<?php

namespace App\Infrastructure\Warehouse\Provider;

use Illuminate\Support\ServiceProvider;

/**
 * Контекст Warehouse: Infra-реализации появятся вместе с таблицами склада.
 */
final class WarehouseServiceProvider extends ServiceProvider
{
    public function register(): void {}
}
