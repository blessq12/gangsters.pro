<?php

namespace App\Filament\Catalog\Resources\ProductResource\Schemas;

use App\Infrastructure\Product\Model\PRD_Tag;
use App\Filament\Catalog\Support\FilamentProductFormMapper;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('productTabs')
                    ->tabs([
                        Tab::make('Основное')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Название')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('articul')
                                    ->label('Артикул')
                                    ->maxLength(64),
                                Textarea::make('description')
                                    ->label('Описание')
                                    ->rows(4)
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                TextInput::make('price_rubles')
                                    ->label('Цена, ₽')
                                    ->numeric(),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visibleOn('edit'),
                                TextInput::make('status_label')
                                    ->label('Статус')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visibleOn('edit'),
                            ]),
                        Tab::make('Питание')
                            ->schema([
                                Section::make('КБЖУ')
                                    ->schema([
                                        TextInput::make('nutrition.calories')
                                            ->label('Ккал')
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('nutrition.proteins')
                                            ->label('Белки')
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('nutrition.fats')
                                            ->label('Жиры')
                                            ->numeric()
                                            ->default(0),
                                        TextInput::make('nutrition.carbs')
                                            ->label('Углеводы')
                                            ->numeric()
                                            ->default(0),
                                        Select::make('nutrition.basis')
                                            ->label('База')
                                            ->options([
                                                'per_100g' => 'На 100 г',
                                                'per_portion' => 'На порцию',
                                            ])
                                            ->default('per_100g'),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Состав')
                            ->schema([
                                Repeater::make('ingredients')
                                    ->label('Ингредиенты')
                                    ->collapsed()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Название')
                                            ->required(),
                                        TextInput::make('amount')
                                            ->label('Количество'),
                                        TextInput::make('unit')
                                            ->label('Ед.'),
                                        Checkbox::make('is_allergen')
                                            ->label('Аллерген'),
                                    ])
                                    ->columns(4)
                                    ->defaultItems(0)
                                    ->addActionLabel('Добавить ингредиент')
                                    ->helperText(
                                        'В форме отображаются первые '.FilamentProductFormMapper::FORM_INGREDIENT_LIMIT.' позиций.',
                                    ),
                            ]),
                        Tab::make('Теги')
                            ->visibleOn('edit')
                            ->schema([
                                CheckboxList::make('tag_codes')
                                    ->label('Теги')
                                    ->options(fn (): array => self::tagOptions())
                                    ->columns(2),
                            ]),
                        Tab::make('Правила корзины')
                            ->visibleOn('edit')
                            ->schema([
                                Toggle::make('counts_as_roll')
                                    ->label('Считается роллом'),
                                Toggle::make('gift_candidate')
                                    ->label('Кандидат в подарок'),
                                Toggle::make('is_complement_set')
                                    ->label('Набор дополнений'),
                            ]),
                        Tab::make('Медиа')
                            ->visibleOn('edit')
                            ->schema([
                                TextInput::make('images_count')
                                    ->label('Изображений')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->numeric(),
                                FileUpload::make('image_upload')
                                    ->label('Загрузить изображение')
                                    ->image()
                                    ->maxFiles(1)
                                    ->storeFiles(false),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function tagOptions(): array
    {
        static $options = null;

        if ($options !== null) {
            return $options;
        }

        $options = [];

        foreach (PRD_Tag::query()->orderBy('sort_order')->orderBy('label')->get(['code', 'label']) as $tag) {
            $options[(string) $tag->code] = (string) $tag->label;
        }

        return $options;
    }
}
