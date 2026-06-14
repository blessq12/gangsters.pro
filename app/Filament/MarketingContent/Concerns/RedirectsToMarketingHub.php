<?php

namespace App\Filament\MarketingContent\Concerns;

use App\Filament\MarketingContent\Resources\MarketingContentResource;

trait RedirectsToMarketingHub
{
    abstract protected static function marketingHubTab(): string;

    protected function getRedirectUrl(): string
    {
        return MarketingContentResource::getUrl('index', [
            'tab' => static::marketingHubTab(),
        ]);
    }
}
