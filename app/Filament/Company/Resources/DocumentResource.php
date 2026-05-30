<?php

namespace App\Filament\Company\Resources;

use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Company\Resources\DocumentResource\Pages\CreateDocument;
use App\Filament\Company\Resources\DocumentResource\Pages\EditDocument;
use App\Filament\Company\Support\RedirectsCompanyIndexToHub;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use App\Infrastructure\SystemContent\Model\SYS_Document;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    use AuthorizesAdminHub;
    use RedirectsCompanyIndexToHub;

    protected static string $companyHubTab = 'documents';

    protected static ?string $model = SYS_Document::class;

    protected static ?string $slug = 'company/documents';

    protected static ?string $modelLabel = 'документ';

    protected static ?string $pluralModelLabel = 'документы';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static bool $shouldRegisterNavigation = false;

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Company;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('Ключ')->required()->maxLength(255),
            TextInput::make('title')->label('Заголовок')->required()->maxLength(255),
            Textarea::make('content')->label('Содержимое')->columnSpanFull(),
            Toggle::make('is_active')->label('Активен')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateDocument::route('/create'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
