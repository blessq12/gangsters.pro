<?php

namespace App\Filament\Catalog\Concerns;

use App\Filament\Catalog\Resources\CatalogResource;

trait RedirectsToCatalogHub
{
    abstract protected static function catalogHubTab(): string;

    protected function getRedirectUrl(): string
    {
        return CatalogResource::getUrl('index', [
            'tab' => static::catalogHubTab(),
        ]);
    }
}
