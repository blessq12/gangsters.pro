<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('tel')
                    ->label('Телефон')
                    ->mask('+7 (999) 999-99-99')
                    ->rule('regex:/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/')
                    ->validationMessages([
                        'regex' => 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX.',
                    ])
                    ->maxLength(255),
                TextInput::make('dob')
                    ->label('Дата рождения'),
            ]);
    }
}
