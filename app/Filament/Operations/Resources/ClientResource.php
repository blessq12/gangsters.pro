<?php

namespace App\Filament\Operations\Resources;

use App\Domain\Admin\Enums\AdminHub;
use App\Filament\Operations\Resources\ClientResource\Pages\CreateClient;
use App\Filament\Operations\Resources\ClientResource\Pages\EditClient;
use App\Filament\Operations\Resources\ClientResource\Schemas\ClientForm;
use App\Filament\Operations\Support\RedirectsOperationsIndexToHub;
use App\Filament\Support\Concerns\AuthorizesAdminHub;
use App\Infrastructure\Client\Model\UR_Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    use AuthorizesAdminHub;
    use RedirectsOperationsIndexToHub;

    protected static string $operationsHubTab = 'clients';

    protected static ?string $model = UR_Client::class;

    protected static ?string $slug = 'operations/clients';

    protected static ?string $modelLabel = 'клиент';

    protected static ?string $pluralModelLabel = 'клиенты';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static bool $shouldRegisterNavigation = false;

    protected static function adminHub(): AdminHub
    {
        return AdminHub::Operations;
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
