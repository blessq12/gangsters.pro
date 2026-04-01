<?php

namespace App\Filament\Resources\ComplimentaryItemRules\Pages;

use App\Filament\Resources\ComplimentaryItemRules\ComplimentaryItemRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComplimentaryItemRule extends EditRecord
{
    protected static string $resource = ComplimentaryItemRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
