<?php

namespace App\Filament\Operations\Resources\OrderResource\Schemas;

use App\Filament\Support\AdminProductSearchQuery;
use App\Infrastructure\Product\Model\PRD_Product;
use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AdminOrderCreateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Клиент')
                    ->schema([
                        TextInput::make('client_id')
                            ->label('ID клиента')
                            ->numeric()
                            ->helperText('Если указан — имя/телефон берутся из профиля клиента.'),
                        TextInput::make('guest_customer_name')->label('Имя (гость)')->maxLength(255),
                        TextInput::make('guest_customer_phone')->label('Телефон (гость)')->tel(),
                        TextInput::make('guest_customer_email')->label('Email (гость)')->email(),
                    ])
                    ->columns(2),
                Section::make('Доставка и оплата')
                    ->schema([
                        Select::make('delivery_method')
                            ->label('Доставка')
                            ->options([
                                DeliveryMethod::Courier->value => 'Курьер',
                                DeliveryMethod::Pickup->value => 'Самовывоз',
                            ])
                            ->required(),
                        Select::make('payment_method')
                            ->label('Оплата')
                            ->options(PaymentMethod::placementOptions())
                            ->required(),
                        TextInput::make('delivery_street')->label('Улица')->columnSpanFull(),
                        TextInput::make('delivery_house')->label('Дом'),
                        TextInput::make('delivery_entrance')->label('Подъезд'),
                        TextInput::make('delivery_apartment')->label('Квартира'),
                        Textarea::make('delivery_comment')->label('Комментарий')->columnSpanFull(),
                    ])
                    ->columns(2),
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
                                        if ($value === null || $value === '') {
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
                                TextInput::make('quantity')
                                    ->label('Кол-во')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Добавить позицию'),
                    ]),
            ]);
    }
}
