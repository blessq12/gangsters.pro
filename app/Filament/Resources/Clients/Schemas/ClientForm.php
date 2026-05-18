<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Domain\Client\Entity\Client;
use App\Support\Client\ClientStatusLabels;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('clientTabs')
                    ->tabs([
                        Tab::make('Профиль')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Имя')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Телефон')
                                    ->placeholder('+7 (900) 123-45-67')
                                    ->mask('+7 (999) 999-99-99')
                                    ->formatStateUsing(self::phoneFormatState(...))
                                    ->dehydrateStateUsing(self::phoneFormatState(...))
                                    ->rule('regex:/^\\+7\\s\\(\\d{3}\\)\\s\\d{3}-\\d{2}-\\d{2}$/')
                                    ->validationMessages([
                                        'regex' => 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX.',
                                    ])
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->nullable(),
                                DatePicker::make('birth_date')
                                    ->label('Дата рождения')
                                    ->nullable(),
                                Select::make('status')
                                    ->label('Статус')
                                    ->options(ClientStatusLabels::statusOptions())
                                    ->required()
                                    ->default(Client::STATUS_ACTIVE),
                            ]),
                        Tab::make('Согласия')
                            ->schema([
                                Section::make()
                                    ->description('Как при регистрации на сайте: согласия влияют на рассылки и обработку данных.')
                                    ->schema([
                                        Toggle::make('consent_personal_data')
                                            ->label('Согласие на обработку персональных данных'),
                                        Toggle::make('consent_marketing')
                                            ->label('Согласие на маркетинговые рассылки'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make()
                    ->description(
                        'Пароль в админке не задаётся. Клиент сможет войти после восстановления пароля на сайте.',
                    )
                    ->visibleOn(['create']),
            ]);
    }

    private static function phoneFormatState(?string $state): ?string
    {
        if ($state === null || $state === '') {
            return $state;
        }

        $digits = preg_replace('/\D+/', '', $state) ?? '';
        if (preg_match('/^7\d{10}$/', $digits) !== 1) {
            return $state;
        }

        return sprintf(
            '+7 (%s) %s-%s-%s',
            substr($digits, 1, 3),
            substr($digits, 4, 3),
            substr($digits, 7, 2),
            substr($digits, 9, 2),
        );
    }
}
