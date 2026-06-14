<?php

namespace App\Filament\Catalog\Resources\ProductResource\Schemas;

use App\Domain\Catalog\Enum\ProductStatus;
use App\Filament\Catalog\Support\FilamentSlugField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(self::allFields());
    }

    /**
     * @return array<int, mixed>
     */
    public static function allFields(): array
    {
        return [
            ...self::cardTabSchema(),
            ...self::tagsTabSchema(),
            ...self::ingredientsTabSchema(),
            ...self::nutritionTabSchema(),
            ...self::metaTabSchema(),
        ];
    }

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

    /**
     * @return array<int, mixed>
     */
    public static function tagsTabSchema(): array
    {
        return [
            Select::make('tags')
                ->label('Теги')
                ->relationship(
                    name: 'tags',
                    titleAttribute: 'label',
                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('sort_order'),
                )
                ->multiple()
                ->preload()
                ->searchable()
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function ingredientsTabSchema(): array
    {
        return [
            TagsInput::make('ingredients')
                ->label('Состав')
                ->placeholder('Добавить ингредиент')
                ->splitKeys(['Tab', ','])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function nutritionTabSchema(): array
    {
        return [
            TextInput::make('calories')
                ->label('Калории')
                ->numeric()
                ->default(0),
            TextInput::make('proteins')
                ->label('Белки')
                ->numeric()
                ->default(0),
            TextInput::make('fats')
                ->label('Жиры')
                ->numeric()
                ->default(0),
            TextInput::make('carbs')
                ->label('Углеводы')
                ->numeric()
                ->default(0),
            Select::make('nutrition_basis')
                ->label('База КБЖУ')
                ->options([
                    'per_100g' => 'На 100 г',
                    'per_portion' => 'На порцию',
                ])
                ->default('per_100g')
                ->required()
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function metaTabSchema(): array
    {
        return [
            Toggle::make('meta_counts_as_roll')
                ->label('Считается как ролл'),
            Toggle::make('meta_gift_candidate')
                ->label('Кандидат на подарок'),
            Toggle::make('meta_is_complement_set')
                ->label('Набор дополнений'),
        ];
    }
}
