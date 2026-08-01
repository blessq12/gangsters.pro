<?php

namespace App\Filament\Content\Company\Resources\OperatorResource\Pages;

use App\Filament\Content\Company\Resources\OperatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOperator extends EditRecord
{
    protected static string $resource = OperatorResource::class;

    protected static ?string $title = 'Редактирование оператора';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (Model $record): bool => OperatorResource::canDelete($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
