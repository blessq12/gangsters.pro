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
                    ->required()
                    ->numeric(),
                TextInput::make('legal_form')
                    ->required(),
                TextInput::make('legal_email')
                    ->email()
                    ->required(),
                TextInput::make('owner')
                    ->required(),
                TextInput::make('inn')
                    ->required(),
                TextInput::make('ogrn')
                    ->required(),
                TextInput::make('okpo')
                    ->required(),
                TextInput::make('kpp')
                    ->required(),
                TextInput::make('registration_address')
                    ->required(),
            ]);
    }
}
