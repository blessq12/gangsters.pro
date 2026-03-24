<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Основное')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Название компании')
                            ->required(),
                        TextInput::make('description')
                            ->label('Описание')
                            ->required(),
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
                    ]),
                Section::make('Контакты')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->mask('+7 (999) 999-99-99')
                            ->formatStateUsing(function (?string $state): ?string {
                                if ($state === null || $state === '') {
                                    return $state;
                                }

                                $digits = preg_replace('/\D+/', '', $state) ?? '';
                                if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                                    $digits = substr($digits, 1);
                                }
                                if (preg_match('/^\d{10}$/', $digits) !== 1) {
                                    return $state;
                                }

                                return sprintf(
                                    '+7 (%s) %s-%s-%s',
                                    substr($digits, 0, 3),
                                    substr($digits, 3, 3),
                                    substr($digits, 6, 2),
                                    substr($digits, 8, 2),
                                );
                            })
                            ->dehydrateStateUsing(function (?string $state): ?string {
                                if ($state === null || $state === '') {
                                    return $state;
                                }

                                $digits = preg_replace('/\D+/', '', $state) ?? '';
                                if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                                    $digits = substr($digits, 1);
                                }
                                if (preg_match('/^\d{10}$/', $digits) !== 1) {
                                    return $state;
                                }

                                return sprintf(
                                    '+7 (%s) %s-%s-%s',
                                    substr($digits, 0, 3),
                                    substr($digits, 3, 3),
                                    substr($digits, 6, 2),
                                    substr($digits, 8, 2),
                                );
                            })
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail): void {
                                    $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
                                    if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                                        return;
                                    }
                                    if (preg_match('/^\d{10}$/', $digits) === 1) {
                                        return;
                                    }

                                    $fail('Телефон должен быть в формате +7 (XXX) XXX-XX-XX.');
                                };
                            })
                            ->required(),
                        TextInput::make('phone_additional')
                            ->label('Доп. телефон')
                            ->mask('+7 (999) 999-99-99')
                            ->formatStateUsing(function (?string $state): ?string {
                                if ($state === null || $state === '') {
                                    return $state;
                                }

                                $digits = preg_replace('/\D+/', '', $state) ?? '';
                                if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                                    $digits = substr($digits, 1);
                                }
                                if (preg_match('/^\d{10}$/', $digits) !== 1) {
                                    return $state;
                                }

                                return sprintf(
                                    '+7 (%s) %s-%s-%s',
                                    substr($digits, 0, 3),
                                    substr($digits, 3, 3),
                                    substr($digits, 6, 2),
                                    substr($digits, 8, 2),
                                );
                            })
                            ->dehydrateStateUsing(function (?string $state): ?string {
                                if ($state === null || $state === '') {
                                    return $state;
                                }

                                $digits = preg_replace('/\D+/', '', $state) ?? '';
                                if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                                    $digits = substr($digits, 1);
                                }
                                if (preg_match('/^\d{10}$/', $digits) !== 1) {
                                    return $state;
                                }

                                return sprintf(
                                    '+7 (%s) %s-%s-%s',
                                    substr($digits, 0, 3),
                                    substr($digits, 3, 3),
                                    substr($digits, 6, 2),
                                    substr($digits, 8, 2),
                                );
                            })
                            ->rule(function () {
                                return function (string $attribute, $value, \Closure $fail): void {
                                    $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
                                    if (preg_match('/^(7|8)\d{10}$/', $digits) === 1) {
                                        return;
                                    }
                                    if (preg_match('/^\d{10}$/', $digits) === 1) {
                                        return;
                                    }

                                    $fail('Доп. телефон должен быть в формате +7 (XXX) XXX-XX-XX.');
                                };
                            })
                            ->required(),
                        TextInput::make('email_address')
                            ->label('Email')
                            ->email()
                            ->required(),
                    ]),
                Section::make('Соцсети')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('vk')
                            ->label('VK'),
                        TextInput::make('inst')
                            ->label('Instagram'),
                    ]),
            ]);
    }
}
