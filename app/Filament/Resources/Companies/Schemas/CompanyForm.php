<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Support\SystemContent\CompanyPhoneField;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('companyTabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Профиль')
                            ->schema(self::profileSections()),
                        Tab::make('Юр. данные')
                            ->schema(CompanyLegalTabSchema::sections('legal')),
                        Tab::make('Зона доставки')
                            ->schema(CompanyDeliveryZoneTabSchema::sections()),
                        Tab::make('Документы')
                            ->id('documents')
                            ->schema(CompanyDocumentsTabSchema::sections()),
                    ]),
            ]);
    }

    /**
     * @return list<Section>
     */
    private static function profileSections(): array
    {
        return [
            Section::make('Основное')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Название компании')
                        ->required(),
                    TextInput::make('brand_name')
                        ->label('Брендовое имя'),
                    TextInput::make('logo')
                        ->label('Логотип (URL или путь)')
                        ->maxLength(500)
                        ->columnSpanFull()
                        ->helperText('Публичный путь к файлу, например /images/logo.png'),
                    TextInput::make('description')
                        ->label('Описание')
                        ->columnSpanFull()
                        ->required(),
                    TextInput::make('tagline')
                        ->label('Слоган')
                        ->columnSpanFull(),
                ]),
            Section::make('Адрес')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
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
                    Textarea::make('address_comment')
                        ->label('Комментарий к адресу')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('city_coverage')
                        ->label('Зона покрытия по городу')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Контакты')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    CompanyPhoneField::make('phone', 'Телефон', true),
                    CompanyPhoneField::make('phone_additional', 'Доп. телефон'),
                    CompanyPhoneField::make('support_phone', 'Телефон поддержки'),
                    CompanyPhoneField::make('whatsapp_phone', 'WhatsApp'),
                    TextInput::make('email_address')
                        ->label('Email')
                        ->email()
                        ->required(),
                    TextInput::make('public_email')
                        ->label('Публичный Email')
                        ->email(),
                ]),
            Section::make('Режим и доставка')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Repeater::make('work_schedule')
                        ->label('Расписание по дням')
                        ->columnSpanFull()
                        ->default(self::defaultSchedule())
                        ->schema([
                            Select::make('day')
                                ->label('День')
                                ->options([
                                    'mon' => 'Понедельник',
                                    'tue' => 'Вторник',
                                    'wed' => 'Среда',
                                    'thu' => 'Четверг',
                                    'fri' => 'Пятница',
                                    'sat' => 'Суббота',
                                    'sun' => 'Воскресенье',
                                ])
                                ->required(),
                            TextInput::make('work')
                                ->label('Работа')
                                ->placeholder('10:00-22:00'),
                            Select::make('is_day_off')
                                ->label('Выходной')
                                ->options([
                                    '0' => 'Нет',
                                    '1' => 'Да',
                                ])
                                ->default('0')
                                ->required(),
                        ])
                        ->columns(3)
                        ->reorderable(false)
                        ->collapsible(),
                    TextInput::make('average_delivery_time_minutes')
                        ->label('Среднее время доставки (мин)')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('min_order_amount_kopecks')
                        ->label('Мин. заказ (коп.)')
                        ->numeric()
                        ->minValue(0),
                    TextInput::make('delivery_fee_kopecks')
                        ->label('Стоимость доставки (коп.)')
                        ->numeric()
                        ->minValue(0),
                ]),
            Section::make('Соцсети и сайт')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('telegram')
                        ->label('Telegram')
                        ->placeholder('@nickname или https://t.me/...')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('vk')
                        ->label('ВКонтакте')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull(),
                    TextInput::make('inst')
                        ->label('Instagram')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull(),
                    TextInput::make('site_url')
                        ->label('Сайт')
                        ->url()
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return list<array{day: string, work: null, is_day_off: string}>
     */
    private static function defaultSchedule(): array
    {
        return [
            ['day' => 'mon', 'work' => null, 'is_day_off' => '0'],
            ['day' => 'tue', 'work' => null, 'is_day_off' => '0'],
            ['day' => 'wed', 'work' => null, 'is_day_off' => '0'],
            ['day' => 'thu', 'work' => null, 'is_day_off' => '0'],
            ['day' => 'fri', 'work' => null, 'is_day_off' => '0'],
            ['day' => 'sat', 'work' => null, 'is_day_off' => '0'],
            ['day' => 'sun', 'work' => null, 'is_day_off' => '0'],
        ];
    }
}
