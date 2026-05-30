<?php

namespace App\Filament\Operations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CartRuleSettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Правила')
                    ->schema([
                        Toggle::make('complement_rule_enabled')
                            ->label('Правило комплекта'),
                        Toggle::make('gift_rule_enabled')
                            ->label('Правило подарка'),
                        TextInput::make('gift_threshold_rubles')
                            ->label('Порог подарка, ₽')
                            ->numeric()
                            ->required(),
                        TextInput::make('rolls_per_complement')
                            ->label('Роллов на комплект')
                            ->numeric()
                            ->integer()
                            ->required(),
                        TextInput::make('complement_rule_sort')
                            ->label('Сортировка комплекта')
                            ->numeric()
                            ->integer()
                            ->required(),
                        TextInput::make('gift_rule_sort')
                            ->label('Сортировка подарка')
                            ->numeric()
                            ->integer()
                            ->required(),
                    ]),
            ]);
    }
}
