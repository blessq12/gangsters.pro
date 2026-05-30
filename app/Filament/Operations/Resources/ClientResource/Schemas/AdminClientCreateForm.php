<?php

namespace App\Filament\Operations\Resources\ClientResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AdminClientCreateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Профиль')
                    ->schema([
                        TextInput::make('name')->label('Имя')->maxLength(255)->required(),
                        TextInput::make('phone')->label('Телефон')->tel()->required(),
                        TextInput::make('email')->label('Email')->email()->maxLength(255),
                        DatePicker::make('birth_date')->label('Дата рождения'),
                        TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->minLength(6)
                            ->helperText('Необязательно — клиент сможет задать пароль позже.'),
                        Toggle::make('consent_personal_data')->label('Согласие на ПДн'),
                        Toggle::make('consent_marketing')->label('Маркетинг'),
                    ])
                    ->columns(2),
            ]);
    }
}
