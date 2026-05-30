<?php

namespace App\Filament\Marketing\Support;

use App\Filament\Marketing\Pages\ManageMarketing;
use Illuminate\Database\Eloquent\Model;

trait RedirectsMarketingIndexToHub
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
        return ManageMarketing::getUrl(
            parameters: [
                'tab' => static::$marketingHubTab,
                ...$parameters,
            ],
            isAbsolute: $isAbsolute,
            panel: $panel,
        );
    }
}
