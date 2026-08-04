<?php

namespace App\Filament\Catalog\Resources\ProductSetResource\Schemas;

use App\Domain\Catalog\Enum\ProductStatus;
use App\Filament\Catalog\Support\FilamentSlugField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ProductSetForm
{
    /**
     * @return array<int, mixed>
     */
    public static function cardTabSchema(): array
    {
        return [
            FilamentSlugField::bindNameToSlug(
                TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
            ),
            FilamentSlugField::make(),
            TextInput::make('sku')
                ->label('Артикул (SKU)')
                ->maxLength(128)
                ->unique(ignoreRecord: true)
                ->helperText('Уникальный артикул набора для внешних систем учёта.'),
            Textarea::make('description')
                ->label('Описание')
                ->columnSpanFull()
                ->rows(4),
            Select::make('status')
                ->label('Статус')
                ->options([
                    ProductStatus::Active->value => 'Активен',
                    ProductStatus::Archived->value => 'В архиве',
                ])
                ->default(ProductStatus::Active->value)
                ->required(),
            TextInput::make('price')
                ->label('Цена, ₽')
                ->numeric()
                ->minValue(0)
                ->suffix('₽'),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(self::cardTabSchema());
    }
}
