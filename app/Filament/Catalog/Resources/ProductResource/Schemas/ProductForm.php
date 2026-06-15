<?php

namespace App\Filament\Catalog\Resources\ProductResource\Schemas;

use App\Domain\Catalog\Enum\ProductStatus;
use App\Filament\Catalog\Support\FilamentSlugField;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(self::createSections());
    }

    /**
     * Семантические блоки формы создания товара (одна страница, сверху вниз).
     *
     * @return array<int, Section>
     */
    public static function createSections(): array
    {
        return [
            Section::make('Карточка товара')
                ->description('Основные поля витрины: название, цена и статус публикации.')
                ->columnSpanFull()
                ->columns(2)
                ->schema(self::cardTabSchema()),
            Section::make('Метки витрины')
                ->description('Теги показываются на карточке и помогают фильтровать меню.')
                ->columnSpanFull()
                ->schema(self::tagsTabSchema()),
            Section::make('Состав блюда')
                ->description('Ингредиенты для детальной карточки. Вводите по одному — Tab или запятая.')
                ->columnSpanFull()
                ->schema(self::ingredientsTabSchema()),
            Section::make('Пищевая ценность')
                ->description('КБЖУ для модального окна товара. Если не нужно — оставьте нули.')
                ->columnSpanFull()
                ->columns(2)
                ->schema(self::nutritionTabSchema()),
            Section::make('Правила checkout')
                ->description('Флаги для корзины и акций. Изображения загружаются после сохранения — на вкладке «Изображения» в редактировании.')
                ->columnSpanFull()
                ->columns(3)
                ->schema(self::metaTabSchema()),
        ];
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
            TextInput::make('sku')
                ->label('Артикул (SKU)')
                ->maxLength(128)
                ->unique(ignoreRecord: true)
                ->helperText('Уникальный артикул товара для внешних систем учёта.'),
            Textarea::make('description')
                ->label('Описание')
                ->columnSpanFull()
                ->rows(4)
                ->helperText('Краткий текст для карточки и модального окна.'),
            Select::make('status')
                ->label('Статус')
                ->options([
                    ProductStatus::Active->value => 'Активен',
                    ProductStatus::Archived->value => 'В архиве',
                ])
                ->default(ProductStatus::Active->value)
                ->required()
                ->helperText('Архивные товары не попадают в публичный каталог.'),
            TextInput::make('price')
                ->label('Цена, ₽')
                ->numeric()
                ->minValue(0)
                ->suffix('₽')
                ->helperText('Цена в рублях. Можно указать позже.'),
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
                ->label('Белки, г')
                ->numeric()
                ->default(0),
            TextInput::make('fats')
                ->label('Жиры, г')
                ->numeric()
                ->default(0),
            TextInput::make('carbs')
                ->label('Углеводы, г')
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
                ->label('Считается как ролл')
                ->helperText('Учитывается при расчёте комплекта дополнений в корзине.'),
            Toggle::make('meta_gift_candidate')
                ->label('Кандидат на подарок')
                ->helperText('Может быть выдан бесплатно по правилам Promotion.'),
            Toggle::make('meta_is_complement_set')
                ->label('Набор дополнений')
                ->helperText('Автодобавление комплекта при достижении порога роллов.'),
        ];
    }
}
