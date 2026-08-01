<?php

namespace App\Filament\Content\Company\Resources\OperatorResource\Pages;

use App\Filament\Content\Company\Resources\OperatorResource;
use Filament\Resources\Pages\ListRecords;

class ListOperators extends ListRecords
{
    protected static string $resource = OperatorResource::class;

    protected static ?string $title = 'Операторы';

    protected static ?string $navigationLabel = 'Операторы';
}
