<?php

namespace App\Filament\Operations\Schemas;

use App\Filament\Forms\Components\YandexDeliveryZoneMap;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DeliveryZoneSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Компания')
                    ->schema([
                        TextInput::make('company_name')
                            ->label('Компания')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
                Section::make('Карта')
                    ->schema([
                        Hidden::make('delivery_zone_geojson'),
                        YandexDeliveryZoneMap::make('delivery_zone_map')
                            ->label('Зона на карте')
                            ->dehydrated(false),
                    ]),
                Section::make('Координаты кухни')
                    ->schema([
                        TextInput::make('kitchen_address')
                            ->label('Адрес кухни (поиск на карте)')
                            ->maxLength(500)
                            ->dehydrated(false),
                        TextInput::make('kitchen_latitude')
                            ->label('Широта')
                            ->numeric(),
                        TextInput::make('kitchen_longitude')
                            ->label('Долгота')
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }
}
