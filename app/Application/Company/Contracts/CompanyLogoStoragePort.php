<?php

namespace App\Application\Company\Contracts;

use Illuminate\Http\UploadedFile;

interface CompanyLogoStoragePort
{
    public function store(UploadedFile $file): string;
}
