<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CompanyProfileSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Основное')
                    ->schema([
                        TextInput::make('name')->label('Название'),
                        TextInput::make('brand_name')->label('Бренд'),
                        Textarea::make('description')->label('Описание')->columnSpanFull(),
                        TextInput::make('tagline')->label('Слоган')->columnSpanFull(),
                        FileUpload::make('logo_upload')
                            ->label('Логотип')
                            ->image()
                            ->disk('public')
                            ->directory('company')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Адрес')
                    ->schema([
                        TextInput::make('country')->label('Страна'),
                        TextInput::make('state')->label('Регион'),
                        TextInput::make('city')->label('Город'),
                        TextInput::make('street')->label('Улица'),
                        TextInput::make('house')->label('Дом'),
                        Textarea::make('address_comment')->label('Комментарий к адресу')->columnSpanFull(),
                        TextInput::make('city_coverage')->label('Зона покрытия (текст)')->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Контакты')
                    ->schema([
                        TextInput::make('phone')->label('Телефон'),
                        TextInput::make('phone_additional')->label('Доп. телефон'),
                        TextInput::make('support_phone')->label('Поддержка'),
                        TextInput::make('whatsapp_phone')->label('WhatsApp'),
                        TextInput::make('email_address')->label('Email'),
                        TextInput::make('public_email')->label('Публичный email'),
                        TextInput::make('telegram')->label('Telegram'),
                        TextInput::make('site_url')->label('Сайт'),
                        TextInput::make('vk')->label('VK'),
                        TextInput::make('inst')->label('Instagram'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Режим работы')
                    ->schema([
                        TextInput::make('work_hours')->label('Часы работы (текст)'),
                        Repeater::make('work_schedule')
                            ->label('Расписание по дням')
                            ->schema([
                                TextInput::make('day')
                                    ->label('День (1–7)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(7)
                                    ->required(),
                                TextInput::make('work')
                                    ->label('Часы работы'),
                                Toggle::make('is_day_off')
                                    ->label('Выходной'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->defaultItems(0),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
