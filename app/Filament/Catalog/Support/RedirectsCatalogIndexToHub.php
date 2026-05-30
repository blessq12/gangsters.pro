<?php

namespace App\Filament\Catalog\Support;

use App\Filament\Catalog\Pages\ManageCatalog;
use Illuminate\Database\Eloquent\Model;

trait RedirectsCatalogIndexToHub
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function getIndexUrl(
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
    ): string {
        return ManageCatalog::getUrl(
            parameters: [
                'tab' => static::$catalogHubTab,
                ...$parameters,
            ],
            isAbsolute: $isAbsolute,
            panel: $panel,
        );
    }
}
