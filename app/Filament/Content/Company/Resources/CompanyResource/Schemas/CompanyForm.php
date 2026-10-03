<?php

namespace App\Filament\Content\Company\Resources\CompanyResource\Schemas;

use App\Filament\Support\FilamentRuPhoneField;
use Filament\Forms\Components\Repeater;
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
    public const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public const DAY_LABELS = [
        'mon' => 'Понедельник',
        'tue' => 'Вторник',
        'wed' => 'Среда',
        'thu' => 'Четверг',
        'fri' => 'Пятница',
        'sat' => 'Суббота',
        'sun' => 'Воскресенье',
    ];

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
                    Textarea::make('description')
                        ->label('Описание')
                        ->columnSpanFull()
                        ->rows(4),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function contactsSchema(): array
    {
        return [
            Section::make('Телефон и почта')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    FilamentRuPhoneField::make('phone', 'Телефон'),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                ]),
            Section::make('Соцсети и сайт')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('social_telegram')
                        ->label('Telegram')
                        ->columnSpanFull()
                        ->maxLength(500),
                    TextInput::make('social_site_url')
                        ->label('Сайт')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('social_vk')
                        ->label('VK')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('social_inst')
                        ->label('Instagram')
                        ->url()
                        ->maxLength(500),
                    FilamentRuPhoneField::make('social_whatsapp', 'WhatsApp'),
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
                ->description('Сохраняется в JSON-поле schedule (объект по дням mon…sun)')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('schedule_rows')
                        ->label('По дням недели')
                        ->columnSpanFull()
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->schema([
                            TextInput::make('day_label')
                                ->label('День')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('day')
                                ->hidden()
                                ->dehydrated()
                                ->required(),
                            TextInput::make('work')
                                ->label('Часы')
                                ->placeholder('10:00–22:00')
                                ->maxLength(64)
                                ->dehydrated(),
                            Toggle::make('is_day_off')
                                ->label('Выходной')
                                ->dehydrated(),
                        ])
                        ->columns(3),
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
                    TextInput::make('legal_full_name')
                        ->label('Полное наименование')
                        ->columnSpanFull()
                        ->maxLength(255),
                    TextInput::make('legal_inn')
                        ->label('ИНН')
                        ->maxLength(12),
                    TextInput::make('legal_ogrn')
                        ->label('ОГРН')
                        ->maxLength(15),
                ]),
        ];
    }

    /**
     * Колонки CMP_company, которые пишутся в модель.
     *
     * @return list<string>
     */
    public static function companyFieldNames(): array
    {
        return [
            'name',
            'description',
            'phone',
            'email',
            'socials',
            'schedule',
        ];
    }

    /**
     * Колонки CMP_company_legal.
     *
     * @return list<string>
     */
    public static function legalFieldNames(): array
    {
        return [
            'full_name',
            'inn',
            'ogrn',
        ];
    }

    /**
     * Ключи объекта socials.
     *
     * @return list<string>
     */
    public static function socialFieldKeys(): array
    {
        return [
            'telegram',
            'vk',
            'inst',
            'site_url',
            'whatsapp',
        ];
    }
}
