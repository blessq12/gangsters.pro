<?php

namespace App\Filament\MarketingContent\Concerns;

use App\Filament\MarketingContent\Resources\MarketingContentResource;
use Illuminate\Database\Eloquent\Model;

trait HasMarketingHubIndexUrl
{
    abstract protected static function marketingHubTab(): string;

    public static function getIndexUrl(
        array $parameters = [],
        bool $isAbsolute = true,
        ?string $panel = null,
        ?Model $tenant = null,
        bool $shouldGuessMissingParameters = false,
    ): string {
        return MarketingContentResource::getUrl('index', [
            ...$parameters,
            'tab' => static::marketingHubTab(),
        ], $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters);
    }
}
