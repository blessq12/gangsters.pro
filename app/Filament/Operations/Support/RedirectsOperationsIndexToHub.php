<?php

namespace App\Filament\Operations\Support;

use App\Filament\Operations\Pages\ManageOperations;
use Illuminate\Database\Eloquent\Model;

trait RedirectsOperationsIndexToHub
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
        return ManageOperations::getUrl(
            parameters: [
                'tab' => static::$operationsHubTab,
                ...$parameters,
            ],
            isAbsolute: $isAbsolute,
            panel: $panel,
        );
    }
}
