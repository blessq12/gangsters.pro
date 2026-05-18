<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Pages\ManageMarketing;
use App\Filament\Resources\Promotions\PromotionResource;
use Filament\Resources\Pages\ListRecords;

class ListPromotions extends ListRecords
{
    protected static string $resource = PromotionResource::class;

    public function mount(): void
    {
        $this->redirect(
            ManageMarketing::getUrl(['tab' => 'promotions']),
            navigate: true
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
