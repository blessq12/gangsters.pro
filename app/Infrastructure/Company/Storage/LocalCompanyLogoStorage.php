<?php

namespace App\Infrastructure\Company\Storage;

use App\Application\Company\Contracts\CompanyLogoStoragePort;
use Illuminate\Http\UploadedFile;

final class LocalCompanyLogoStorage implements CompanyLogoStoragePort
{
    public function store(UploadedFile $file): string
    {
        return $file->store('company', 'public');
    }
}
