<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // поле image в БД пока обязательное, используем desktop-версию
        $data['image'] = $data['image_desktop'] ?? ($data['image'] ?? null);
        return $data;
    }
}

