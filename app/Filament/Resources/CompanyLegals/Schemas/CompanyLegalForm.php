<?php

namespace App\Filament\Resources\CompanyLegals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyLegalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Реквизиты')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('full_name')
                            ->label('Полное наименование')
                            ->columnSpanFull(),
                        TextInput::make('short_name')
                            ->label('Краткое наименование'),
                        TextInput::make('legal_form')
                            ->label('Орг. форма')
                            ->required(),
                        TextInput::make('owner')
                            ->label('Владелец')
                            ->required(),
                        TextInput::make('responsible_person')
                            ->label('Ответственный'),
                        TextInput::make('responsible_position')
                            ->label('Должность ответственного'),
                        TextInput::make('legal_email')
                            ->label('Юр. Email')
                            ->email()
                            ->required(),
                        TextInput::make('contracts_email')
                            ->label('Email для договоров')
                            ->email(),
                        TextInput::make('legal_phone')
                            ->label('Юр. телефон')
                            ->mask('+7 (999) 999-99-99')
                            ->columnSpanFull(),
                    ]),
                Section::make('Регистрационные данные')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('inn')
                            ->label('ИНН')
                            ->required(),
                        TextInput::make('kpp')
                            ->label('КПП')
                            ->required(),
                        TextInput::make('ogrn')
                            ->label('ОГРН')
                            ->required(),
                        TextInput::make('ogrnip')
                            ->label('ОГРНИП'),
                        TextInput::make('okpo')
                            ->label('ОКПО')
                            ->required(),
                        TextInput::make('tax_system')
                            ->label('Система налогообложения'),
                        Toggle::make('is_vat_payer')
                            ->label('Плательщик НДС')
                            ->inline(false),
                        TextInput::make('vat_rate_default')
                            ->label('Ставка НДС, %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20),
                    ]),
                Section::make('Адреса')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('registration_address')
                            ->label('Юридический адрес')
                            ->required(),
                        TextInput::make('actual_address')
                            ->label('Фактический адрес'),
                        TextInput::make('postal_address')
                            ->label('Почтовый адрес'),
                    ]),
                Section::make('Банк')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Банк')
                            ->columnSpanFull(),
                        TextInput::make('bik')
                            ->label('БИК'),
                        TextInput::make('checking_account')
                            ->label('Расчетный счет'),
                        TextInput::make('correspondent_account')
                            ->label('Корр. счет'),
                    ]),
            ]);
    }
}
