<?php

namespace App\Filament\Resources\ComplimentaryItemRules\Pages;

use App\Filament\Resources\ComplimentaryItemRules\ComplimentaryItemRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComplimentaryItemRules extends ListRecords
{
    protected static string $resource = ComplimentaryItemRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
