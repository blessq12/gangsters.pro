<?php

namespace App\Filament\Content\MarketingContent\Concerns;

use App\Filament\Content\MarketingContent\Resources\MarketingContentResource;

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
