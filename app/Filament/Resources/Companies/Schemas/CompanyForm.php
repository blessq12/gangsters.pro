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
                    ->label('Название компании')
                    ->required(),
                TextInput::make('description')
                    ->label('Описание')
                    ->required(),
                TextInput::make('country')
                    ->label('Страна')
                    ->required(),
                TextInput::make('state')
                    ->label('Регион')
                    ->required(),
                TextInput::make('city')
                    ->label('Город')
                    ->required(),
                TextInput::make('street')
                    ->label('Улица')
                    ->required(),
                TextInput::make('house')
                    ->label('Дом')
                    ->required(),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required(),
                TextInput::make('phone_additional')
                    ->label('Доп. телефон')
                    ->tel()
                    ->required(),
                TextInput::make('email_address')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('logo')
                    ->label('Логотип (URL)')
                    ->required()
                    ->default('http://via.placeholder.com/150x150'),
                TextInput::make('vk'),
                TextInput::make('inst'),
            ]);
    }
}
