<?php

namespace App\Filament\Company\Resources;

use App\Filament\Company\Resources\StaffUserResource\Pages\CreateStaffUser;
use App\Filament\Company\Resources\StaffUserResource\Pages\EditStaffUser;
use App\Filament\Company\Support\RedirectsCompanyIndexToHub;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StaffUserResource extends Resource
{
    use RedirectsCompanyIndexToHub;

    protected static string $companyHubTab = 'staff';

    protected static ?string $model = User::class;

    protected static ?string $slug = 'company/staff';

    protected static ?string $modelLabel = 'сотрудник';

    protected static ?string $pluralModelLabel = 'сотрудники';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Имя')->required()->maxLength(255),
            TextInput::make('email')->label('Email')->email()->required()->maxLength(255),
            TextInput::make('tel')->label('Телефон')->maxLength(50),
            TextInput::make('dob')->label('Дата рождения'),
            TextInput::make('password')
                ->label('Пароль')
                ->password()
                ->dehydrated(fn (?string $state): bool => filled($state))
                ->required(fn (string $operation): bool => $operation === 'create'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateStaffUser::route('/create'),
            'edit' => EditStaffUser::route('/{record}/edit'),
        ];
    }
}
