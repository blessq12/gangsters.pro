<?php

namespace App\Filament\Content\Company\Resources;

use App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages\CreateCompanyDocument;
use App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages\EditCompanyDocument;
use App\Filament\Content\Company\Resources\CompanyDocumentResource\Pages\ListCompanyDocuments;
use App\Filament\Content\Company\Resources\CompanyDocumentResource\Schemas\CompanyDocumentForm;
use App\Filament\Content\Company\Resources\CompanyDocumentResource\Tables\CompanyDocumentsTable;
use App\Filament\Support\AdminNavigationGroup;
use App\Infrastructure\Content\Model\CMP_CompanyDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyDocumentResource extends Resource
{
    protected static ?string $model = CMP_CompanyDocument::class;

    protected static ?string $navigationLabel = 'Документы';

    protected static ?string $slug = 'company-documents';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Документ';

    protected static ?string $pluralModelLabel = 'Документы';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 15;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Organization;

    public static function form(Schema $schema): Schema
    {
        return CompanyDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyDocuments::route('/'),
            'create' => CreateCompanyDocument::route('/create'),
            'edit' => EditCompanyDocument::route('/{record}/edit'),
        ];
    }
}
