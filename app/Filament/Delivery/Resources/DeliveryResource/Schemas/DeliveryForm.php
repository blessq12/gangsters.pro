<?php

namespace App\Filament\Delivery\Resources\DeliveryResource\Schemas;

use App\Filament\Delivery\Forms\Components\YandexDeliveryZoneMap;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class DeliveryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Tabs::make('delivery-context')
                    ->columnSpanFull()
                    ->tabs([
                        'settings' => Tab::make('settings')
                            ->label('Настройки')
                            ->icon(Heroicon::OutlinedCog6Tooth)
                            ->schema(self::settingsSchema()),
                        'zone' => Tab::make('zone')
                            ->label('Зона доставки')
                            ->icon(Heroicon::OutlinedMap)
                            ->schema(self::zoneSchema()),
                    ]),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function settingsSchema(): array
    {
        return [
            Section::make('Тарифы и сроки')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    self::moneyInput('min_order_amount_kopecks', 'Минимальная сумма заказа'),
                    self::moneyInput('delivery_fee_kopecks', 'Стоимость доставки'),
                    self::moneyInput('outside_zone_delivery_fee_kopecks', 'Доставка за пределами зоны'),
                    TextInput::make('average_delivery_time_minutes')
                        ->label('Среднее время доставки')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(999)
                        ->suffix('мин'),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function zoneSchema(): array
    {
        return [
            Section::make('Адрес кухни')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('kitchen_address')
                        ->label('Адрес для поиска на карте')
                        ->columnSpanFull()
                        ->maxLength(500),
                    TextInput::make('kitchen_city')
                        ->label('Город')
                        ->maxLength(255),
                    TextInput::make('kitchen_street')
                        ->label('Улица')
                        ->maxLength(255),
                    TextInput::make('kitchen_house')
                        ->label('Дом')
                        ->maxLength(63),
                    TextInput::make('kitchen_address_comment')
                        ->label('Комментарий к адресу')
                        ->columnSpanFull()
                        ->maxLength(255),
                    Hidden::make('kitchen_latitude'),
                    Hidden::make('kitchen_longitude'),
                ]),
            Section::make('Полигон зоны')
                ->columnSpanFull()
                ->schema([
                    YandexDeliveryZoneMap::make('delivery_zone_geojson')
                        ->label('Зона доставки')
                        ->columnSpanFull(),
                ]),
        ];
    }

    private static function moneyInput(string $field, string $label): TextInput
    {
        return TextInput::make($field)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->suffix('₽')
            ->formatStateUsing(static function (mixed $state): mixed {
                if ($state === null || $state === '') {
                    return null;
                }

                return ((int) $state) / 100;
            })
            ->dehydrateStateUsing(static function (mixed $state): ?int {
                if ($state === null || $state === '') {
                    return null;
                }

                return (int) round(((float) $state) * 100);
            });
    }
}
