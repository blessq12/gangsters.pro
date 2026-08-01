<?php

namespace App\Filament\Crm\Resources\ClientResource\Schemas;

use App\Filament\Support\FilamentRuPhoneField;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClientViewSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Профиль')
                    ->columns(2)
                    ->schema([
                        TextInput::make('id')
                            ->label('ID')
                            ->disabled(),
                        TextInput::make('name')
                            ->label('Имя')
                            ->disabled(),
                        FilamentRuPhoneField::makeReadOnly('phone'),
                        TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->placeholder('—'),
                        DatePicker::make('birth_date')
                            ->label('Дата рождения')
                            ->disabled()
                            ->native(false)
                            ->displayFormat('d.m.Y'),
                        TextInput::make('created_at')
                            ->label('Регистрация')
                            ->disabled(),
                        Toggle::make('consent_personal_data')
                            ->label('Согласие на ПДн')
                            ->disabled(),
                        Toggle::make('consent_marketing')
                            ->label('Согласие на маркетинг')
                            ->disabled(),
                    ]),
                Section::make('Адреса')
                    ->schema([
                        Repeater::make('addresses')
                            ->label('')
                            ->disabled()
                            ->dehydrated(false)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Название')
                                    ->placeholder('—'),
                                TextInput::make('type')
                                    ->label('Тип')
                                    ->placeholder('—'),
                                TextInput::make('street')
                                    ->label('Улица'),
                                TextInput::make('house')
                                    ->label('Дом'),
                                TextInput::make('entrance')
                                    ->label('Подъезд')
                                    ->placeholder('—'),
                                TextInput::make('apartment')
                                    ->label('Квартира')
                                    ->placeholder('—'),
                                TextInput::make('comment')
                                    ->label('Комментарий')
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                                Toggle::make('is_default')
                                    ->label('По умолчанию')
                                    ->disabled(),
                            ])
                            ->itemLabel(fn (array $state): ?string => self::addressItemLabel($state)),
                    ]),
                Section::make('Избранное')
                    ->schema([
                        TextInput::make('favorite_product_ids_label')
                            ->label('ID товаров')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private static function addressItemLabel(array $state): ?string
    {
        $street = trim((string) ($state['street'] ?? ''));
        $house = trim((string) ($state['house'] ?? ''));
        if ($street === '' && $house === '') {
            return null;
        }

        $line = trim($street.' '.$house);
        if (! empty($state['is_default'])) {
            $line .= ' · по умолчанию';
        }

        return $line !== '' ? $line : null;
    }
}
