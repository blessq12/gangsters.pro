<?php

namespace App\Filament\Content\Company\Resources;

use App\Filament\Content\Company\Resources\OperatorResource\Pages\CreateOperator;
use App\Filament\Content\Company\Resources\OperatorResource\Pages\EditOperator;
use App\Filament\Content\Company\Resources\OperatorResource\Pages\ListOperators;
use App\Filament\Content\Company\Resources\OperatorResource\Schemas\OperatorForm;
use App\Filament\Content\Company\Resources\OperatorResource\Tables\OperatorsTable;
use App\Filament\Support\AdminNavigationGroup;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OperatorResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Операторы';

    protected static ?string $slug = 'operators';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Оператор';

    protected static ?string $pluralModelLabel = 'Операторы';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 20;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Organization;

    public static function form(Schema $schema): Schema
    {
        return OperatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OperatorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperators::route('/'),
            'create' => CreateOperator::route('/create'),
            'edit' => EditOperator::route('/{record}/edit'),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return (int) $record->getKey() !== (int) auth()->id();
    }
}
