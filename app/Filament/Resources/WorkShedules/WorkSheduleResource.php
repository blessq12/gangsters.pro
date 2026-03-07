<?php

namespace App\Filament\Resources\WorkShedules;

use App\Filament\Resources\WorkShedules\Pages\CreateWorkShedule;
use App\Filament\Resources\WorkShedules\Pages\EditWorkShedule;
use App\Filament\Resources\WorkShedules\Pages\ListWorkShedules;
use App\Filament\Resources\WorkShedules\Schemas\WorkSheduleForm;
use App\Filament\Resources\WorkShedules\Tables\WorkShedulesTable;
use App\Models\WorkShedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkSheduleResource extends Resource
{
    protected static ?string $model = WorkShedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WorkSheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkShedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkShedules::route('/'),
            'create' => CreateWorkShedule::route('/create'),
            'edit' => EditWorkShedule::route('/{record}/edit'),
        ];
    }
}
