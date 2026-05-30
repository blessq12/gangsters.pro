<?php

namespace App\Application\Marketing\Contracts;

use Illuminate\Http\UploadedFile;

interface MarketingMediaStoragePort
{
    public function store(UploadedFile $file, string $directory): string;

    public function delete(?string $path): void;
}
