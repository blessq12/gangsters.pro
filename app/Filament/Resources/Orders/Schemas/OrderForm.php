<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Infrastructure\Client\Model\UR_Client;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                Tabs::make('orderTabs')
                    ->tabs([
                        Tab::make('Заказ')
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
                                            ->options(OrderStatusLabels::statusOptions())
                                            ->required(),
                                    ]),
                                TextInput::make('created_at')
                                    ->label('Создан')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($state): ?string {
                                        if ($state === null) {
                                            return null;
                                        }

                                        try {
                                            return \Illuminate\Support\Carbon::parse($state)->format('d.m.Y H:i');
                                        } catch (\Throwable) {
                                            return (string) $state;
                                        }
                                    }),
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
                                                    : null,
                                            ),
                                        TextInput::make('total')
                                            ->label('Итого, ₽')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(
                                                fn ($state): ?string => $state !== null
                                                    ? Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))
                                                    : null,
                                            ),
                                        TextInput::make('discount_total')
                                            ->label('Скидка, ₽')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(
                                                fn ($state): ?string => $state !== null && (int) $state !== 0
                                                    ? Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))
                                                    : Money::formatRublesRuAdaptive(0),
                                            ),
                                    ]),
                            ]),
                        Tab::make('Клиент')
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
                                                $set('customer_email', $client->email ?? '');
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
                                TextInput::make('customer_email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->disabled(fn (Get $get): bool => (int) $get('client_id') !== 0)
                                    ->dehydrated(true)
                                    ->columnSpanFull(),
                                Section::make('Адрес клиента')
                                    ->schema(self::addressFields('customer_address_'))
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Доставка')
                            ->schema([
                                Select::make('delivery_method')
                                    ->label('Способ доставки')
                                    ->options(DeliveryMethod::options())
                                    ->required()
                                    ->columnSpanFull(),
                                Section::make('Адрес доставки')
                                    ->schema(self::addressFields('delivery_address_'))
                                    ->columnSpanFull(),
                                Textarea::make('delivery_comment')
                                    ->label('Комментарий к доставке')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Оплата')
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Способ оплаты')
                                    ->options(PaymentMethod::placementOptions())
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('payment_status')
                                    ->label('Статус оплаты')
                                    ->options(PaymentStatus::options())
                                    ->native(false)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, TextInput>
     */
    private static function addressFields(string $prefix): array
    {
        return [
            Grid::make()
                ->columns(2)
                ->schema([
                    TextInput::make($prefix.'street')
                        ->label('Улица')
                        ->maxLength(255),
                    TextInput::make($prefix.'house')
                        ->label('Дом')
                        ->maxLength(63),
                    TextInput::make($prefix.'entrance')
                        ->label('Подъезд')
                        ->maxLength(31),
                    TextInput::make($prefix.'apartment')
                        ->label('Квартира')
                        ->maxLength(31),
                ]),
        ];
    }
}
