<?php

namespace App\Filament\Resources\DeliveryZones\Schemas;

use App\Application\SystemContent\Support\CompanyKitchenAddressFormatter;
use App\Filament\Forms\Components\YandexDeliveryZoneMap;
use App\Filament\Resources\Companies\CompanyResource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

final class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Зона бесплатной доставки')
                    ->description('Полигон на карте — область бесплатной доставки. Адрес кухни задаётся в карточке «Компании».')
                    ->columnSpanFull()
                    ->schema([
                        Placeholder::make('kitchen_address_preview')
                            ->label('Адрес кухни (для центрирования карты)')
                            ->content(function ($record): HtmlString|string {
                                if ($record === null) {
                                    return '—';
                                }
                                $line = CompanyKitchenAddressFormatter::format($record);
                                $editUrl = CompanyResource::getUrl('edit', ['record' => $record]);

                                $html = $line !== ''
                                    ? e($line).' · <a href="'.e($editUrl).'" class="text-primary-600 underline" target="_blank" rel="noopener">Изменить в «Компании»</a>'
                                    : 'Заполните адрес в <a href="'.e($editUrl).'" class="text-primary-600 underline" target="_blank" rel="noopener">«Компании»</a>.';

                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                        YandexDeliveryZoneMap::make('delivery_zone_geojson')
                            ->label('Полигон на карте')
                            ->helperText('Нарисуйте контур в редакторе, нажмите «Применить», затем сохраните форму.')
                            ->columnSpanFull(),
                        Hidden::make('kitchen_latitude'),
                        Hidden::make('kitchen_longitude'),
                    ]),
            ]);
    }
}
