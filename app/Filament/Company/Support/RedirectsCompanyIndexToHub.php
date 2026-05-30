<?php

namespace App\Filament\Company\Support;

use App\Filament\Company\Pages\ManageCompany;
use Illuminate\Database\Eloquent\Model;

trait RedirectsCompanyIndexToHub
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
        return ManageCompany::getUrl(
            parameters: [
                'tab' => static::$companyHubTab,
                ...$parameters,
            ],
            isAbsolute: $isAbsolute,
            panel: $panel,
        );
    }
}
