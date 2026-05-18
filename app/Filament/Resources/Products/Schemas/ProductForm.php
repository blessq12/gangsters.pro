<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Support\Money;
use App\Support\Product\ProductStatusLabels;
use App\Support\Product\TagColorLabels;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('productTabs')
                    ->tabs([
                        Tab::make('Основное')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->label('Название'),
                                TextInput::make('articul')
                                    ->label('Артикул')
                                    ->maxLength(255),
                                Select::make('status')
                                    ->label('Статус')
                                    ->options(ProductStatusLabels::options())
                                    ->required()
                                    ->default(ProductEntity::STATUS_ACTIVE),
                                TextInput::make('price')
                                    ->label('Цена, ₽ (2 знака)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->formatStateUsing(function ($state): ?float {
                                        if ($state === null || $state === '') {
                                            return null;
                                        }

                                        return Money::kopecksToApiRubles((int) $state);
                                    })
                                    ->dehydrateStateUsing(function ($state): ?int {
                                        return Money::apiRublesToKopecks($state);
                                    }),
                                Textarea::make('description')
                                    ->label('Описание')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Витрина')
                            ->schema([
                                Select::make('tags')
                                    ->label('Теги')
                                    ->relationship('tags', 'label')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('label')
                                            ->label('Текст бейджа')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('color')
                                            ->label('Цвет бейджа')
                                            ->required()
                                            ->default('amber')
                                            ->options(TagColorLabels::options()),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('КБЖУ')
                            ->schema([
                                Select::make('nutrition_basis')
                                    ->label('База КБЖУ')
                                    ->options([
                                        'per_100g' => 'На 100 г',
                                        'per_portion' => 'На порцию',
                                    ])
                                    ->default('per_100g')
                                    ->required()
                                    ->live(),
                                TextInput::make('calories')
                                    ->numeric()
                                    ->label(fn (Get $get): string => $get('nutrition_basis') === 'per_portion'
                                        ? 'Ккал на порцию'
                                        : 'Ккал на 100 г'),
                                TextInput::make('proteins')
                                    ->numeric()
                                    ->label(fn (Get $get): string => $get('nutrition_basis') === 'per_portion'
                                        ? 'Белки на порцию'
                                        : 'Белки на 100 г'),
                                TextInput::make('fats')
                                    ->numeric()
                                    ->label(fn (Get $get): string => $get('nutrition_basis') === 'per_portion'
                                        ? 'Жиры на порцию'
                                        : 'Жиры на 100 г'),
                                TextInput::make('carbs')
                                    ->numeric()
                                    ->label(fn (Get $get): string => $get('nutrition_basis') === 'per_portion'
                                        ? 'Углеводы на порцию'
                                        : 'Углеводы на 100 г'),
                            ]),
                        Tab::make('Корзина')
                            ->schema([
                                Section::make('Серверная корзина')
                                    ->description(
                                        'Эти переключатели не связаны с «Тегами» на вкладке «Витрина»: теги — для сайта, здесь — логика корзины и заказа.'
                                    )
                                    ->schema([
                                        Toggle::make('cart_rule_counts_as_roll')
                                            ->label('1. Считать в промо «комплект к роллам»')
                                            ->helperText(
                                                'Вкл.: каждая единица увеличивает счётчик «роллов». При N единицах сервер добавляет товар из пункта 2 («Заказы → Правила корзины»).'
                                            )
                                            ->default(false),
                                        Toggle::make('cart_rule_is_complement_set')
                                            ->label('2. Товар «комплект», который подставляет сервер')
                                            ->helperText(
                                                'Вкл.: позиция подставляется системой (соус, имбирь и т.д.). Можно отметить несколько товаров.'
                                            )
                                            ->default(false),
                                        Toggle::make('cart_rule_gift_candidate')
                                            ->label('3. Можно выбрать бесплатным подарком')
                                            ->helperText(
                                                'Вкл.: товар в списке подарков при сумме корзины ≥ порога. Клиент выбирает в чекауте.'
                                            )
                                            ->default(false),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
