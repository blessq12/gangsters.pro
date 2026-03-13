<?php

namespace App\Filament\Resources\CompanyLegals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyLegalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')
                    ->label('Компания (ID)')
                    ->required()
                    ->numeric(),
                TextInput::make('legal_form')
                    ->label('Орг. форма')
                    ->required(),
                TextInput::make('legal_email')
                    ->label('Юр. Email')
                    ->email()
                    ->required(),
                TextInput::make('owner')
                    ->label('Владелец')
                    ->required(),
                TextInput::make('inn')
                    ->label('ИНН')
                    ->required(),
                TextInput::make('ogrn')
                    ->label('ОГРН')
                    ->required(),
                TextInput::make('okpo')
                    ->label('ОКПО')
                    ->required(),
                TextInput::make('kpp')
                    ->label('КПП')
                    ->required(),
                TextInput::make('registration_address')
                    ->label('Юридический адрес')
                    ->required(),
            ]);
    }
}
