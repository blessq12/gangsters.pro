<?php

namespace App\Filament\Operations\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

final class ActiveCartDetailSchema
{
    /**
     * @return array<int, Section>
     */
    public static function components(): array
    {
        return [
            Section::make('Ошибка загрузки')
                ->visible(fn (Get $get): bool => (bool) $get('load_error'))
                ->schema([
                    Textarea::make('load_error_message')
                        ->label('')
                        ->disabled()
                        ->dehydrated(false)
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Клиент')
                ->visible(fn (Get $get): bool => ! (bool) $get('load_error'))
                ->schema([
                    TextInput::make('client_label')
                        ->label('Имя')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('client_type_label')
                        ->label('Тип')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('client_phone')
                        ->label('Телефон')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('—'),
                    TextInput::make('client_email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('—'),
                    TextInput::make('client_edit_url')
                        ->label('Профиль клиента')
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn (Get $get): bool => filled($get('client_edit_url'))),
                ])
                ->columns(2),
            Section::make('Сессия')
                ->visible(fn (Get $get): bool => ! (bool) $get('load_error'))
                ->schema([
                    TextInput::make('session_id')
                        ->label('ID сессии')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('session_public_id')
                        ->label('Public ID')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    TextInput::make('session_updated_at')
                        ->label('Обновлена')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('session_expires_at')
                        ->label('Истекает')
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('session_created_at')
                        ->label('Создана')
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->columns(2),
            Section::make('Корзина')
                ->visible(fn (Get $get): bool => ! (bool) $get('load_error'))
                ->description(fn (Get $get): ?string => $get('cart_summary'))
                ->schema([
                    self::readOnlyRepeater('cart_lines')
                        ->schema([
                            TextInput::make('product_name')
                                ->label('Товар')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('quantity')
                                ->label('Кол-во')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('unit_price_label')
                                ->label('Цена')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('—'),
                            TextInput::make('line_total_label')
                                ->label('Сумма')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('—'),
                        ])
                        ->columns(4),
                ]),
            Section::make('Избранное')
                ->visible(fn (Get $get): bool => ! (bool) $get('load_error'))
                ->description(fn (Get $get): string => (string) $get('favorites_count').' товар(ов)')
                ->schema([
                    self::readOnlyRepeater('favorite_items')
                        ->schema([
                            TextInput::make('product_name')
                                ->label('Товар')
                                ->disabled()
                                ->dehydrated(false),
                            TextInput::make('product_id')
                                ->label('ID')
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(2),
                ]),
            Section::make('Черновик оформления')
                ->visible(fn (Get $get): bool => ! (bool) $get('load_error'))
                ->schema([
                    TextInput::make('checkout_draft_status')
                        ->label('Статус')
                        ->disabled()
                        ->dehydrated(false),
                    self::readOnlyKeyValue('checkout_guest', 'Контакт гостя'),
                    self::readOnlyKeyValue('checkout_delivery', 'Доставка'),
                    self::readOnlyKeyValue('checkout_payment', 'Оплата'),
                    self::readOnlyKeyValue('checkout_promotions', 'Акции'),
                ]),
        ];
    }

    private static function readOnlyRepeater(string $name): Repeater
    {
        return Repeater::make($name)
            ->label('')
            ->addable(false)
            ->deletable(false)
            ->reorderable(false)
            ->disabled()
            ->dehydrated(false);
    }

    private static function readOnlyKeyValue(string $name, string $label): KeyValue
    {
        return KeyValue::make($name)
            ->label($label)
            ->disabled()
            ->dehydrated(false)
            ->visible(fn (Get $get): bool => ($get($name) ?? []) !== []);
    }
}
