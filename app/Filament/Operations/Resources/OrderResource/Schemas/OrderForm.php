<?php

namespace App\Filament\Operations\Resources\OrderResource\Schemas;

use App\Filament\Support\AdminProductSearchQuery;
use App\Infrastructure\Product\Model\PRD_Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Заказ')
                    ->schema([
                        TextInput::make('id')
                            ->label('ID')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('status_label')
                            ->label('Статус')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('created_at')
                            ->label('Создан')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),
                Section::make('Клиент')
                    ->schema([
                        TextInput::make('customer_name')->label('Имя')->disabled()->dehydrated(false),
                        TextInput::make('customer_phone')->label('Телефон')->disabled()->dehydrated(false),
                        TextInput::make('customer_email')->label('Email')->disabled()->dehydrated(false),
                        TextInput::make('customer_address')->label('Адрес клиента')->disabled()->dehydrated(false)->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Доставка и оплата')
                    ->schema([
                        TextInput::make('delivery_method')->label('Способ доставки')->disabled()->dehydrated(false),
                        TextInput::make('delivery_address')->label('Адрес доставки')->disabled()->dehydrated(false)->columnSpanFull(),
                        TextInput::make('delivery_comment')->label('Комментарий')->disabled()->dehydrated(false)->columnSpanFull(),
                        TextInput::make('payment_method')->label('Способ оплаты')->disabled()->dehydrated(false),
                        TextInput::make('payment_status')->label('Статус оплаты')->disabled()->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make('Суммы')
                    ->schema([
                        TextInput::make('subtotal')->label('Подытог, ₽')->disabled()->dehydrated(false),
                        TextInput::make('discount_total')->label('Скидка, ₽')->disabled()->dehydrated(false),
                        TextInput::make('delivery_fee')->label('Доставка, ₽')->disabled()->dehydrated(false),
                        TextInput::make('total')->label('Итого, ₽')->disabled()->dehydrated(false),
                    ])
                    ->columns(4),
                Section::make('Позиции')
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Товар')
                                    ->searchable()
                                    ->getSearchResultsUsing(fn (string $search): array => app(AdminProductSearchQuery::class)->optionsForSelect(
                                        search: $search !== '' ? $search : null,
                                        limit: 30,
                                    ))
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        if (! filled($value)) {
                                            return null;
                                        }

                                        $product = PRD_Product::query()->find((int) $value);
                                        if ($product === null) {
                                            return (string) $value;
                                        }

                                        return trim((string) $product->name)
                                            .(filled($product->articul) ? ' ('.$product->articul.')' : '');
                                    })
                                    ->required(),
                                TextInput::make('product_label')
                                    ->label('Текущее название')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn ($get): bool => filled($get('product_label'))),
                                TextInput::make('quantity')
                                    ->label('Кол-во')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),
                                TextInput::make('row_total')
                                    ->label('Сумма, ₽')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->minItems(1),
                    ]),
            ]);
    }
}
