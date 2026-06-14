<?php

namespace App\Domain\Client\Event;

final readonly class ClientUnauthorizedAccessDetected
{
    public function __construct(
        public string $path,
        public string $method,
        public string $ip,
        public ?string $userAgent,
    ) {}
}
