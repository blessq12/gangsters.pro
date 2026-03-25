<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // legacy: поле image в БД пока обязательное, используем desktop-версию
        $data['image'] = $data['image_desktop'] ?? ($data['image'] ?? null);
        return $data;
    }
}

