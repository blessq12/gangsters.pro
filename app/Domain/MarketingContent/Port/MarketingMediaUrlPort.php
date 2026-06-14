<?php

namespace App\Domain\MarketingContent\Port;

/**
 * Публичный URL медиа-файла витрины (storage path → абсолютный URL для SPA).
 */
interface MarketingMediaUrlPort
{
    public function resolve(?string $path): ?string;
}
