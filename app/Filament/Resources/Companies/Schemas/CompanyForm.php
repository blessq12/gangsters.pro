<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('country')
                    ->required(),
                TextInput::make('state')
                    ->required(),
                TextInput::make('city')
                    ->required(),
                TextInput::make('street')
                    ->required(),
                TextInput::make('house')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('phone_additional')
                    ->tel()
                    ->required(),
                TextInput::make('email_address')
                    ->email()
                    ->required(),
                TextInput::make('logo')
                    ->required()
                    ->default('http://via.placeholder.com/150x150'),
                TextInput::make('vk'),
                TextInput::make('inst'),
            ]);
    }
}
