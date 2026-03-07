<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eatsId'),
                TextInput::make('restaurantId'),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('name'),
                TextInput::make('tel')
                    ->tel(),
                TextInput::make('street'),
                TextInput::make('house'),
                TextInput::make('building'),
                TextInput::make('staircase'),
                TextInput::make('floor'),
                TextInput::make('apartment'),
                Textarea::make('deliveryAddress')
                    ->columnSpanFull(),
                TextInput::make('full_address'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                DateTimePicker::make('deliveryDate'),
                TextInput::make('deliveryType'),
                TextInput::make('total')
                    ->numeric(),
                TextInput::make('itemsCost')
                    ->numeric(),
                TextInput::make('deliveryFee')
                    ->numeric(),
                TextInput::make('change')
                    ->numeric(),
                Textarea::make('promos')
                    ->columnSpanFull(),
                Toggle::make('delivery')
                    ->required(),
                TextInput::make('comment'),
                TextInput::make('personQty')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('payType')
                    ->required()
                    ->default('cash'),
                TextInput::make('status')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('frontpad_id'),
                TextInput::make('discriminator'),
            ]);
    }
}
