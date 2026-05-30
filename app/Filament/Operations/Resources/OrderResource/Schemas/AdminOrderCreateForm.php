<?php

namespace App\Filament\Operations\Resources\OrderResource\Schemas;

use App\Application\Catalog\Query\GetAdminProductListQuery;
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
                                    ->getSearchResultsUsing(function (string $search): array {
                                        $result = app(GetAdminProductListQuery::class)->execute(
                                            search: $search !== '' ? $search : null,
                                            status: 'active',
                                            page: 1,
                                            perPage: 30,
                                        );

                                        $options = [];
                                        foreach ($result['items'] as $product) {
                                            $options[(int) $product['id']] = $product['name'].' ('.$product['articul'].')';
                                        }

                                        return $options;
                                    })
                                    ->getOptionLabelUsing(function ($value): ?string {
                                        if ($value === null || $value === '') {
                                            return null;
                                        }

                                        $result = app(GetAdminProductListQuery::class)->execute(
                                            search: (string) $value,
                                            page: 1,
                                            perPage: 1,
                                        );

                                        $product = $result['items'][0] ?? null;

                                        return $product
                                            ? $product['name'].' ('.$product['articul'].')'
                                            : (string) $value;
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
