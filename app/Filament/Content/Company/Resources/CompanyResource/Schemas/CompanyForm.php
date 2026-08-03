<?php

namespace App\Filament\Content\Company\Resources\CompanyResource\Schemas;

use App\Filament\Support\FilamentRuPhoneField;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Tabs::make('company-context')
                    ->columnSpanFull()
                    ->tabs([
                        'profile' => Tab::make('profile')
                            ->label('Профиль')
                            ->icon(Heroicon::OutlinedBuildingOffice2)
                            ->schema(self::profileSchema()),
                        'contacts' => Tab::make('contacts')
                            ->label('Контакты')
                            ->icon(Heroicon::OutlinedPhone)
                            ->schema(self::contactsSchema()),
                        'schedule' => Tab::make('schedule')
                            ->label('Расписание')
                            ->icon(Heroicon::OutlinedClock)
                            ->schema(self::scheduleSchema()),
                        'legal' => Tab::make('legal')
                            ->label('Юрлицо')
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->schema(self::legalSchema()),
                    ]),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function profileSchema(): array
    {
        return [
            Section::make('Профиль компании')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Название')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('brand_name')
                        ->label('Бренд')
                        ->maxLength(255),
                    TextInput::make('tagline')
                        ->label('Слоган')
                        ->columnSpanFull()
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('Описание')
                        ->columnSpanFull()
                        ->rows(4),
                    TextInput::make('logo')
                        ->label('Логотип (URL)')
                        ->columnSpanFull()
                        ->maxLength(500),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function contactsSchema(): array
    {
        return [
            Section::make('Телефоны и почта')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    FilamentRuPhoneField::make('phone', 'Телефон'),
                    FilamentRuPhoneField::make('phone_additional', 'Доп. телефон'),
                    FilamentRuPhoneField::make('support_phone', 'Телефон поддержки'),
                    FilamentRuPhoneField::make('whatsapp_phone', 'WhatsApp'),
                    TextInput::make('email_address')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('public_email')
                        ->label('Публичный email')
                        ->email()
                        ->maxLength(255),
                ]),
            Section::make('Соцсети и сайт')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('telegram')
                        ->label('Telegram')
                        ->columnSpanFull()
                        ->maxLength(500),
                    TextInput::make('site_url')
                        ->label('Сайт')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('vk')
                        ->label('VK')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('inst')
                        ->label('Instagram')
                        ->url()
                        ->maxLength(500),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function scheduleSchema(): array
    {
        return [
            Section::make('Режим работы')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('work_hours')
                        ->label('Краткая строка режима')
                        ->maxLength(255)
                        ->placeholder('10:00–22:00'),
                    Repeater::make('work_schedule')
                        ->label('По дням недели')
                        ->columnSpanFull()
                        ->defaultItems(7)
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
                                ->label('Часы')
                                ->placeholder('10:00–22:00')
                                ->maxLength(63),
                            Toggle::make('is_day_off')
                                ->label('Выходной')
                                ->default(false),
                        ])
                        ->columns(3)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function legalSchema(): array
    {
        return [
            Section::make('Реквизиты')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('legal_full_name')->label('Полное наименование')->columnSpanFull(),
                    TextInput::make('legal_short_name')->label('Краткое наименование'),
                    TextInput::make('legal_legal_form')->label('Орг.-правовая форма'),
                    TextInput::make('legal_inn')->label('ИНН')->maxLength(12),
                    TextInput::make('legal_kpp')->label('КПП')->maxLength(9),
                    TextInput::make('legal_ogrn')->label('ОГРН')->maxLength(15),
                    TextInput::make('legal_ogrnip')->label('ОГРНИП')->maxLength(15),
                    TextInput::make('legal_okpo')->label('ОКПО')->maxLength(10),
                    TextInput::make('legal_tax_system')->label('Система налогообложения'),
                    Toggle::make('legal_is_vat_payer')->label('Плательщик НДС'),
                    TextInput::make('legal_vat_rate_default')->label('Ставка НДС по умолчанию')->numeric()->minValue(0)->maxValue(100),
                    TextInput::make('legal_owner')->label('Владелец')->columnSpanFull(),
                    TextInput::make('legal_responsible_person')->label('Ответственное лицо'),
                    TextInput::make('legal_responsible_position')->label('Должность'),
                    TextInput::make('legal_legal_email')->label('Юр. email')->email(),
                    TextInput::make('legal_contracts_email')->label('Email по договорам')->email(),
                    FilamentRuPhoneField::make('legal_legal_phone', 'Юр. телефон'),
                    Textarea::make('legal_registration_address')->label('Юр. адрес')->columnSpanFull()->rows(2),
                    Textarea::make('legal_actual_address')->label('Фактический адрес')->columnSpanFull()->rows(2),
                    Textarea::make('legal_postal_address')->label('Почтовый адрес')->columnSpanFull()->rows(2),
                    TextInput::make('legal_bank_name')->label('Банк')->columnSpanFull(),
                    TextInput::make('legal_bik')->label('БИК')->maxLength(9),
                    TextInput::make('legal_checking_account')->label('Расчётный счёт')->maxLength(20),
                    TextInput::make('legal_correspondent_account')->label('Корр. счёт')->maxLength(20),
                ]),
        ];
    }

    /**
     * @return list<string>
     */
    public static function companyFieldNames(): array
    {
        return [
            'name',
            'brand_name',
            'description',
            'tagline',
            'phone',
            'phone_additional',
            'support_phone',
            'whatsapp_phone',
            'email_address',
            'public_email',
            'work_hours',
            'work_schedule',
            'logo',
            'telegram',
            'site_url',
            'vk',
            'inst',
        ];
    }

    /**
     * @return list<string>
     */
    public static function legalFieldNames(): array
    {
        return [
            'full_name',
            'short_name',
            'legal_form',
            'legal_email',
            'contracts_email',
            'legal_phone',
            'owner',
            'responsible_person',
            'responsible_position',
            'inn',
            'ogrn',
            'ogrnip',
            'okpo',
            'kpp',
            'tax_system',
            'is_vat_payer',
            'vat_rate_default',
            'registration_address',
            'actual_address',
            'postal_address',
            'bank_name',
            'bik',
            'checking_account',
            'correspondent_account',
        ];
    }
}
