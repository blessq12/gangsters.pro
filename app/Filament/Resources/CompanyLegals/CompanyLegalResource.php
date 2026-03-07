<?php

namespace App\Filament\Resources\CompanyLegals;

use App\Filament\Resources\CompanyLegals\Pages\CreateCompanyLegal;
use App\Filament\Resources\CompanyLegals\Pages\EditCompanyLegal;
use App\Filament\Resources\CompanyLegals\Pages\ListCompanyLegals;
use App\Filament\Resources\CompanyLegals\Schemas\CompanyLegalForm;
use App\Filament\Resources\CompanyLegals\Tables\CompanyLegalsTable;
use App\Models\CompanyLegal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyLegalResource extends Resource
{
    protected static ?string $model = CompanyLegal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
            'create' => CreateCompanyLegal::route('/create'),
            'edit' => EditCompanyLegal::route('/{record}/edit'),
        ];
    }
}
