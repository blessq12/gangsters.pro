<?php

namespace App\Filament\Crm\Resources;

use App\Filament\Crm\Resources\ClientResource\Pages\ListClients;
use App\Filament\Crm\Resources\ClientResource\Pages\ViewClient;
use App\Filament\Crm\Resources\ClientResource\RelationManagers\OrderHistoryRelationManager;
use App\Filament\Crm\Resources\ClientResource\Schemas\ClientViewSchema;
use App\Filament\Crm\Resources\ClientResource\Tables\ClientsTable;
use App\Filament\Support\AdminNavigationGroup;
use App\Infrastructure\Crm\Model\CRM_Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClientResource extends Resource
{
    protected static ?string $model = CRM_Client::class;

    protected static ?string $navigationLabel = 'Клиенты';

    protected static ?string $slug = 'clients';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Клиент';

    protected static ?string $pluralModelLabel = 'Клиенты';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 20;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Sales;

    public static function form(Schema $schema): Schema
    {
        return ClientViewSchema::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OrderHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'view' => ViewClient::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
