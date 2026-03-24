<?php

namespace App\Filament\Resources\CompanyLegals;

use App\Filament\Resources\CompanyLegals\Pages\EditCompanyLegal;
use App\Filament\Resources\CompanyLegals\Pages\ListCompanyLegals;
use App\Filament\Resources\CompanyLegals\Schemas\CompanyLegalForm;
use App\Filament\Resources\CompanyLegals\Tables\CompanyLegalsTable;
use App\Infrastructure\SystemContent\Model\SYS_CompanyLegal;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyLegalResource extends Resource
{
    protected static ?string $model = SYS_CompanyLegal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Юр. данные компании';

    // Группа: данные о компании
    protected static string|UnitEnum|null $navigationGroup = 'Компания';

    // Сортировка в навигации внутри блока компании
    protected static ?int $navigationSort = 51;

    public static function form(Schema $schema): Schema
    {
        return CompanyLegalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyLegalsTable::configure($table);
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
            'index' => ListCompanyLegals::route('/'),
            'edit' => EditCompanyLegal::route('/{record}/edit'),
        ];
    }
}
