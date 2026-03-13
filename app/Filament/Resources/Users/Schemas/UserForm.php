<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('tel')
                    ->label('Телефон')
                    ->tel(),
                TextInput::make('coins')
                    ->label('Монеты')
                    ->required()
                    ->default('0'),
                DateTimePicker::make('email_verified_at')
                    ->label('Email подтверждён'),
                TextInput::make('password')
                    ->label('Пароль')
                    ->password()
                    ->required(),
                TextInput::make('dob')
                    ->label('Дата рождения'),
                TextInput::make('token_to_reset_password')
                    ->label('Токен сброса пароля')
                    ->password(),
            ]);
    }
}
