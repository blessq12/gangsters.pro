<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('frontpad_api_key'),
                Toggle::make('use_coin_system')
                    ->required(),
                TextInput::make('coin_system_percent')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('use_discount_system')
                    ->required(),
                TextInput::make('discount_system_percent')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
