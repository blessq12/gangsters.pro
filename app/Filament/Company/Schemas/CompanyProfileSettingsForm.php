<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                Section::make('Режим и доставка')
                    ->schema([
                        TextInput::make('work_hours')->label('Часы работы (текст)'),
                        TextInput::make('delivery_hours')->label('Часы доставки (текст)'),
                        Textarea::make('work_schedule_json')
                            ->label('Расписание (JSON)')
                            ->helperText('Массив объектов: day, work, is_day_off')
                            ->columnSpanFull(),
                        TextInput::make('min_order_amount_kopecks')->label('Мин. заказ (коп.)')->numeric(),
                        TextInput::make('delivery_fee_kopecks')->label('Доставка (коп.)')->numeric(),
                        TextInput::make('average_delivery_time_minutes')->label('Среднее время доставки (мин)')->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
