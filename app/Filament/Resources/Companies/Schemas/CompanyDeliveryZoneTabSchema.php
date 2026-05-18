<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Application\SystemContent\Support\CompanyKitchenAddressFormatter;
use App\Filament\Forms\Components\YandexDeliveryZoneMap;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;

final class CompanyDeliveryZoneTabSchema
{
    /**
     * @return list<Section>
     */
    public static function sections(): array
    {
        return [
            Section::make('Зона бесплатной доставки')
                ->description('Полигон на карте — область бесплатной доставки. Адрес кухни задаётся на вкладке «Профиль».')
                ->columnSpanFull()
                ->schema([
                    Placeholder::make('kitchen_address_preview')
                        ->label('Адрес кухни (для центрирования карты)')
                        ->content(function ($record): string {
                            if ($record === null) {
                                return '—';
                            }

                            return CompanyKitchenAddressFormatter::format($record) ?: 'Заполните адрес на вкладке «Профиль».';
                        })
                        ->columnSpanFull(),
                    YandexDeliveryZoneMap::make('delivery_zone_geojson')
                        ->label('Полигон на карте')
                        ->helperText('Нарисуйте контур в редакторе, нажмите «Применить», затем сохраните форму.')
                        ->columnSpanFull(),
                    Hidden::make('kitchen_latitude'),
                    Hidden::make('kitchen_longitude'),
                ]),
        ];
    }
}
