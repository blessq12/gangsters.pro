<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Support\SystemContent\CompanyPhoneField;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

final class CompanyLegalTabSchema
{
    /**
     * @return list<Section>
     */
    public static function sections(string $statePath = 'legal'): array
    {
        $prefix = $statePath === '' ? '' : $statePath.'.';

        return [
            Section::make('Реквизиты')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make($prefix.'full_name')
                        ->label('Полное наименование')
                        ->columnSpanFull(),
                    TextInput::make($prefix.'short_name')
                        ->label('Краткое наименование'),
                    TextInput::make($prefix.'legal_form')
                        ->label('Орг. форма')
                        ->required(),
                    TextInput::make($prefix.'owner')
                        ->label('Владелец')
                        ->required(),
                    TextInput::make($prefix.'responsible_person')
                        ->label('Ответственный'),
                    TextInput::make($prefix.'responsible_position')
                        ->label('Должность ответственного'),
                    TextInput::make($prefix.'legal_email')
                        ->label('Юр. Email')
                        ->email()
                        ->required(),
                    TextInput::make($prefix.'contracts_email')
                        ->label('Email для договоров')
                        ->email(),
                    CompanyPhoneField::make($prefix.'legal_phone', 'Юр. телефон')
                        ->columnSpanFull(),
                ]),
            Section::make('Регистрационные данные')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make($prefix.'inn')
                        ->label('ИНН')
                        ->required(),
                    TextInput::make($prefix.'kpp')
                        ->label('КПП')
                        ->required(),
                    TextInput::make($prefix.'ogrn')
                        ->label('ОГРН')
                        ->required(),
                    TextInput::make($prefix.'ogrnip')
                        ->label('ОГРНИП'),
                    TextInput::make($prefix.'okpo')
                        ->label('ОКПО')
                        ->required(),
                    TextInput::make($prefix.'tax_system')
                        ->label('Система налогообложения'),
                    Toggle::make($prefix.'is_vat_payer')
                        ->label('Плательщик НДС')
                        ->inline(false),
                    TextInput::make($prefix.'vat_rate_default')
                        ->label('Ставка НДС, %')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(20),
                ]),
            Section::make('Адреса')
                ->columns(1)
                ->columnSpanFull()
                ->schema([
                    TextInput::make($prefix.'registration_address')
                        ->label('Юридический адрес')
                        ->required(),
                    TextInput::make($prefix.'actual_address')
                        ->label('Фактический адрес'),
                    TextInput::make($prefix.'postal_address')
                        ->label('Почтовый адрес'),
                ]),
            Section::make('Банк')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make($prefix.'bank_name')
                        ->label('Банк')
                        ->columnSpanFull(),
                    TextInput::make($prefix.'bik')
                        ->label('БИК'),
                    TextInput::make($prefix.'checking_account')
                        ->label('Расчетный счет'),
                    TextInput::make($prefix.'correspondent_account')
                        ->label('Корр. счет'),
                ]),
        ];
    }
}
