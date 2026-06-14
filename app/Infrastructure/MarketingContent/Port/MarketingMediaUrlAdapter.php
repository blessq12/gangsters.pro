<?php

namespace App\Infrastructure\MarketingContent\Port;

use App\Domain\MarketingContent\Port\MarketingMediaUrlPort;
use App\Infrastructure\MarketingContent\Support\PublicMediaUrl;

final class MarketingMediaUrlAdapter implements MarketingMediaUrlPort
{
    public function resolve(?string $path): ?string
    {
        return PublicMediaUrl::resolve($path);
    }
}
