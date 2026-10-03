<?php

namespace App\Filament\Content\Delivery\Resources\DeliveryResource\Schemas;

use App\Filament\Content\Delivery\Forms\Components\YandexDeliveryZoneMap;
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
                            ->label('Зоны доставки')
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
                ->description('После изменения адреса координаты пересчитываются (геокодер). Точка на карте зон обновится после blur поля или сохранения.')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('kitchen_address')
                        ->label('Адрес для поиска на карте')
                        ->columnSpanFull()
                        ->maxLength(500)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (...$args) => self::refreshKitchenOnLivewire($args)),
                    TextInput::make('kitchen_city')
                        ->label('Город')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (...$args) => self::refreshKitchenOnLivewire($args)),
                    TextInput::make('kitchen_street')
                        ->label('Улица')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (...$args) => self::refreshKitchenOnLivewire($args)),
                    TextInput::make('kitchen_house')
                        ->label('Дом')
                        ->maxLength(63)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (...$args) => self::refreshKitchenOnLivewire($args)),
                    TextInput::make('kitchen_address_comment')
                        ->label('Комментарий к адресу')
                        ->columnSpanFull()
                        ->maxLength(255),
                    Hidden::make('kitchen_latitude')->dehydrated(),
                    Hidden::make('kitchen_longitude')->dehydrated(),
                ]),
            Section::make('Полигоны зон')
                ->description('У каждой зоны своя цена и флаг «отдалённый район». Вне всех зон доставка недоступна.')
                ->columnSpanFull()
                ->schema([
                    YandexDeliveryZoneMap::make('delivery_zones')
                        ->label('Зоны доставки')
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

    /**
     * @param  list<mixed>  $args
     */
    private static function refreshKitchenOnLivewire(array $args): void
    {
        foreach ($args as $arg) {
            if (is_object($arg) && method_exists($arg, 'refreshKitchenCoordinates')) {
                $arg->refreshKitchenCoordinates();

                return;
            }
        }
    }
}
