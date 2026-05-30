<?php

namespace App\Filament\Company\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CompanyLegalSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Реквизиты')
                    ->schema([
                        TextInput::make('full_name')->label('Полное наименование')->columnSpanFull(),
                        TextInput::make('short_name')->label('Краткое наименование'),
                        TextInput::make('legal_form')->label('ОПФ'),
                        TextInput::make('inn')->label('ИНН'),
                        TextInput::make('ogrn')->label('ОГРН'),
                        TextInput::make('ogrnip')->label('ОГРНИП'),
                        TextInput::make('okpo')->label('ОКПО'),
                        TextInput::make('kpp')->label('КПП'),
                        TextInput::make('tax_system')->label('Система налогообложения'),
                        Toggle::make('is_vat_payer')->label('Плательщик НДС'),
                        TextInput::make('vat_rate_default')->label('Ставка НДС по умолчанию')->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Контакты и ответственные')
                    ->schema([
                        TextInput::make('legal_email')->label('Юр. email'),
                        TextInput::make('contracts_email')->label('Договорной email'),
                        TextInput::make('legal_phone')->label('Телефон'),
                        TextInput::make('owner')->label('Владелец'),
                        TextInput::make('responsible_person')->label('Ответственное лицо'),
                        TextInput::make('responsible_position')->label('Должность'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                Section::make('Адреса')
                    ->schema([
                        TextInput::make('registration_address')->label('Юридический')->columnSpanFull(),
                        TextInput::make('actual_address')->label('Фактический')->columnSpanFull(),
                        TextInput::make('postal_address')->label('Почтовый')->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Банк')
                    ->schema([
                        TextInput::make('bank_name')->label('Банк')->columnSpanFull(),
                        TextInput::make('bik')->label('БИК'),
                        TextInput::make('checking_account')->label('Р/с'),
                        TextInput::make('correspondent_account')->label('К/с'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
