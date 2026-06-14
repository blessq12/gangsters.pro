<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\CompanyResource\Pages\ManageCompany;
use App\Filament\Support\AdminNavigationGroup;
use App\Infrastructure\Company\Model\CMP_Company;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyResource extends Resource
{
    protected static ?string $model = CMP_Company::class;

    protected static ?string $navigationLabel = 'Компания';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $slug = 'company';

    protected static ?string $modelLabel = 'Компания';

    protected static ?string $pluralModelLabel = 'Компания';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Organization;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCompany::route('/'),
        ];
    }
}
