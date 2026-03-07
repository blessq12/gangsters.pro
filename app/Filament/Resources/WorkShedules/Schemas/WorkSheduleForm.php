<?php

namespace App\Filament\Resources\WorkShedules\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkSheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_id')
                    ->required()
                    ->numeric(),
                TextInput::make('day')
                    ->required(),
                TextInput::make('open_time')
                    ->required(),
                TextInput::make('close_time')
                    ->required(),
                Toggle::make('day_off')
                    ->required(),
                TextInput::make('day_eng')
                    ->required(),
            ]);
    }
}
