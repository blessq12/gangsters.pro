<?php

namespace App\Filament\Content\Company\Resources\OperatorResource\Pages;

use App\Filament\Content\Company\Resources\OperatorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOperator extends CreateRecord
{
    protected static string $resource = OperatorResource::class;

    protected static ?string $title = 'Новый оператор';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
