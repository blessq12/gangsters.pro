<?php

namespace App\Infrastructure\Production\Provider;

use Illuminate\Support\ServiceProvider;

/**
 * Контекст Production: Infra-реализации появятся вместе с таблицами тикетов кухни.
 */
final class ProductionServiceProvider extends ServiceProvider
{
    public function register(): void {}
}
