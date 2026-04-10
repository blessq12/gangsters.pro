<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Infrastructure\Client\Model\UR_Client;
use App\Support\Money;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Заказ')
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('id')
                                    ->label('ID заказа')
                                    ->disabled()
                                    ->dehydrated(false),
                                Select::make('status')
                                    ->label('Статус заказа')
                                    ->options([
                                        'new' => 'Новый',
                                        'preparing' => 'Готовится',
                                        'in_transit' => 'В пути',
                                        'delivered' => 'Доставлен',
                                    ])
                                    ->required(),
                            ]),
                        Grid::make()
                            ->columns(3)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Подытог, ₽')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(
                                        fn ($state): ?string => $state !== null
                                            ? Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))
                                            : null
                                    ),
                                TextInput::make('total')
                                    ->label('Итого, ₽')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(
                                        fn ($state): ?string => $state !== null
                                            ? Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))
                                            : null
                                    ),
                                TextInput::make('discount_total')
                                    ->label('Скидка, ₽')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(
                                        fn ($state): ?string => $state !== null && (int) $state !== 0
                                            ? Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))
                                            : Money::formatRublesRuAdaptive(0)
                                    ),
                            ]),
                    ]),
                Section::make('Клиент')
                    ->schema([
                        Select::make('client_id')
                            ->label('Клиент')
                            ->default(0)
                            ->options(function (): array {
                                $guests = [0 => 'Гость (указать данные вручную)'];
                                $clients = UR_Client::query()
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (UR_Client $c) => [
                                        $c->id => $c->name.' — '.($c->phone ?? '').($c->email ? ' ('.$c->email.')' : ''),
                                    ])
                                    ->all();

                                return $guests + $clients;
                            })
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                if ($state > 0) {
                                    $client = UR_Client::find($state);
                                    if ($client) {
                                        $set('customer_name', $client->name);
                                        $set('customer_phone', $client->phone ?? '');
                                    }
                                }
                            })
                            ->columnSpanFull(),
                        TextInput::make('customer_name')
                            ->label('Имя')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => (int) $get('client_id') === 0)
                            ->disabled(fn (Get $get): bool => (int) $get('client_id') !== 0)
                            ->dehydrated(true)
                            ->columnSpanFull(),
                        TextInput::make('customer_phone')
                            ->label('Телефон')
                            ->tel()
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => (int) $get('client_id') === 0)
                            ->disabled(fn (Get $get): bool => (int) $get('client_id') !== 0)
                            ->dehydrated(true)
                            ->columnSpanFull(),
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('customer_address_street')
                                    ->label('Улица')
                                    ->maxLength(255),
                                TextInput::make('customer_address_house')
                                    ->label('Дом')
                                    ->maxLength(63),
                                TextInput::make('customer_address_entrance')
                                    ->label('Подъезд')
                                    ->maxLength(31),
                                TextInput::make('customer_address_apartment')
                                    ->label('Квартира')
                                    ->maxLength(31),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make('Доставка и оплата')
                    ->schema([
                        Select::make('delivery_method')
                            ->label('Способ доставки')
                            ->options(DeliveryMethod::options())
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(
                                function ($state): ?string {
                                    if ($state === null) {
                                        return null;
                                    }

                                    if (! is_array($state)) {
                                        return (string) $state;
                                    }

                                    $parts = array_filter([
                                        $state['street'] ?? null,
                                        $state['house'] ?? null,
                                        $state['entrance'] ?? null,
                                        $state['apartment'] ?? null,
                                    ]);

                                    return $parts === []
                                        ? null
                                        : implode(', ', $parts);
                                }
                            )
                            ->columnSpanFull(),
                        Textarea::make('delivery_comment')
                            ->label('Комментарий к доставке')
                            ->rows(2)
                            ->columnSpanFull(),
                        Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options(PaymentMethod::options())
                            ->required()
                            ->columnSpanFull(),
                        Select::make('payment_status')
                            ->label('Статус оплаты')
                            ->options(PaymentStatus::options())
                            ->native(false)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
