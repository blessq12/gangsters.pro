<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use App\Infrastructure\Client\Model\UR_Client;
use App\Models\User;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use UnitEnum;

class ManageUsers extends Page implements HasTable
{
    use InteractsWithTable {
        makeTable as makeBaseTable;
    }

    protected static string $routePath = 'people';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $title = 'Пользователи';

    protected static string|UnitEnum|null $navigationGroup = 'Пользователи';

    protected static ?int $navigationSort = 30;

    #[Url(as: 'tab')]
    public string $usersTab = 'clients';

    public function mount(): void
    {
        $this->usersTab = $this->normalizeUsersTab($this->usersTab);
    }

    public function setUsersTab(string $tab): void
    {
        $tab = $this->normalizeUsersTab($tab);

        if ($this->usersTab === $tab) {
            return;
        }

        $this->usersTab = $tab;
        $this->resetTable();
    }

    public function updatedUsersTab(): void
    {
        $this->usersTab = $this->normalizeUsersTab($this->usersTab);
        $this->resetTable();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(fn (): string => match ($this->usersTab) {
                    'admins' => 'Создать администратора',
                    default => 'Создать клиента',
                })
                ->url(fn (): string => $this->createUrlForActiveTab()),
        ];
    }

    public function table(Table $table): Table
    {
        return match ($this->usersTab) {
            'admins' => UsersTable::configure($table, UserResource::class),
            default => ClientsTable::configure($table, ClientResource::class),
        };
    }

    protected function makeTable(): Table
    {
        return $this->makeBaseTable()
            ->query(fn (): Builder => $this->getTableQuery());
    }

    protected function getTableQuery(): Builder
    {
        return match ($this->usersTab) {
            'admins' => User::query(),
            default => UR_Client::query(),
        };
    }

    protected function getTableQueryStringIdentifier(): ?string
    {
        return 'users_'.$this->usersTab;
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('usersTabs')
                    ->id('users')
                    ->columnSpanFull()
                    ->livewireProperty('usersTab')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        'clients' => Tab::make('Клиенты'),
                        'admins' => Tab::make('Администраторы'),
                    ]),
                EmbeddedTable::make()
                    ->key(fn (): string => 'users-table-'.$this->usersTab),
            ]);
    }

    private function createUrlForActiveTab(): string
    {
        return match ($this->usersTab) {
            'admins' => UserResource::getUrl('create'),
            default => ClientResource::getUrl('create'),
        };
    }

    private function normalizeUsersTab(string $tab): string
    {
        if (! in_array($tab, ['clients', 'admins'], true)) {
            return 'clients';
        }

        return $tab;
    }
}
