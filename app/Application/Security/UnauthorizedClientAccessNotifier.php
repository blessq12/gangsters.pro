<?php

namespace App\Application\Security;

interface UnauthorizedClientAccessNotifier
{
    public function notify(string $path, string $method, string $ip, ?string $userAgent): void;
}

