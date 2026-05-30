<?php

namespace App\Filament\Operations\Resources\ClientResource\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Профиль')
                    ->schema([
                        TextInput::make('name')->label('Имя')->disabled()->dehydrated(false),
                        TextInput::make('phone')->label('Телефон')->disabled()->dehydrated(false),
                        TextInput::make('email')->label('Email')->disabled()->dehydrated(false),
                        TextInput::make('status')->label('Статус')->disabled()->dehydrated(false),
                        TextInput::make('birth_date')->label('Дата рождения')->disabled()->dehydrated(false),
                        TextInput::make('created_at')->label('Регистрация')->disabled()->dehydrated(false),
                        Toggle::make('consent_personal_data')->label('Согласие на ПДн')->disabled()->dehydrated(false),
                        Toggle::make('consent_marketing')->label('Маркетинг')->disabled()->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Адреса')
                    ->schema([
                        Repeater::make('addresses')
                            ->label('')
                            ->schema([
                                TextInput::make('type')->label('Тип')->disabled()->dehydrated(false),
                                TextInput::make('title')->label('Название')->disabled()->dehydrated(false),
                                TextInput::make('street')->label('Улица')->disabled()->dehydrated(false),
                                TextInput::make('house')->label('Дом')->disabled()->dehydrated(false),
                                TextInput::make('entrance')->label('Подъезд')->disabled()->dehydrated(false),
                                TextInput::make('apartment')->label('Квартира')->disabled()->dehydrated(false),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->dehydrated(false),
                    ]),
                Section::make('Заказы клиента')
                    ->schema([
                        Repeater::make('orders')
                            ->label('')
                            ->schema([
                                TextInput::make('id')->label('ID')->disabled()->dehydrated(false),
                                TextInput::make('status_label')->label('Статус')->disabled()->dehydrated(false),
                                TextInput::make('total')->label('Сумма, ₽')->disabled()->dehydrated(false),
                                TextInput::make('created_at')->label('Создан')->disabled()->dehydrated(false),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
