<?php

namespace App\Filament\Catalog\Support;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

final class FilamentSlugField
{
    public static function make(string $name = 'slug'): TextInput
    {
        return TextInput::make($name)
            ->label('Слаг')
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->alphaDash();
    }

    public static function bindNameToSlug(TextInput $nameField): TextInput
    {
        return $nameField
            ->live(onBlur: true)
            ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                if ($operation !== 'create' || blank($state)) {
                    return;
                }

                $set('slug', Str::slug($state));
            });
    }
}
