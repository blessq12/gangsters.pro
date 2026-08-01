<?php

namespace App\Filament\Content\Company\Resources\OperatorResource\Schemas;

use App\Filament\Support\FilamentRuPhoneField;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

final class OperatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Оператор')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Имя')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        FilamentRuPhoneField::make('tel', 'Телефон')
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === Operation::Create->value)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $operation): ?string => $operation === Operation::Edit->value
                                ? 'Оставьте пустым, чтобы не менять'
                                : null),
                    ]),
            ]);
    }
}
