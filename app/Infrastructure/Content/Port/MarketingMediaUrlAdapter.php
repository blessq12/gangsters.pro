<?php

namespace App\Infrastructure\Content\Port;

use App\Domain\Content\Port\MarketingMediaUrlPort;
use App\Infrastructure\Content\Support\PublicMediaUrl;

final class MarketingMediaUrlAdapter implements MarketingMediaUrlPort
{
    public function resolve(?string $path): ?string
    {
        return PublicMediaUrl::resolve($path);
    }
}
