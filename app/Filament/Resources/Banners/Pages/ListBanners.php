<?php

namespace App\Filament\Resources\Banners\Pages;

use App\Filament\Pages\ManageMarketing;
use App\Filament\Resources\Banners\BannerResource;
use Filament\Resources\Pages\ListRecords;

class ListBanners extends ListRecords
{
    protected static string $resource = BannerResource::class;

    public function mount(): void
    {
        $this->redirect(
            ManageMarketing::getUrl(['tab' => 'banners']),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
