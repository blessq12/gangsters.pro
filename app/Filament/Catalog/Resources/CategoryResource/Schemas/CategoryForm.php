<?php

namespace App\Filament\Catalog\Resources\CategoryResource\Schemas;

use App\Filament\Catalog\Support\FilamentSlugField;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class CategoryForm
{
    /**
     * @return array<int, mixed>
     */
    public static function tabSchema(): array
    {
        return [
            FilamentSlugField::bindNameToSlug(
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
            ),
            FilamentSlugField::make(),
            Toggle::make('is_active')
                ->label('Активна')
                ->default(true),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(self::tabSchema());
    }
}
