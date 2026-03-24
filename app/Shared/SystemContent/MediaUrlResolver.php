<?php

namespace App\Shared\SystemContent;

interface MediaUrlResolver
{
    public function resolve(?string $path): ?string;
}

