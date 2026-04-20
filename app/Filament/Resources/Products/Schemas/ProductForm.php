<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Support\Money;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->label('Название'),
                TextInput::make('articul')
                    ->label('Артикул')
                    ->maxLength(255),
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активен',
                        'archived' => 'Архив',
                    ])
                    ->required()
                    ->default('active'),
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
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
                TextInput::make('calories')
                    ->numeric()
                    ->label('Ккал на 100г'),
                TextInput::make('proteins')
                    ->numeric()
                    ->label('Белки на 100г'),
                TextInput::make('fats')
                    ->numeric()
                    ->label('Жиры на 100г'),
                TextInput::make('carbs')
                    ->numeric()
                    ->label('Углеводы на 100г'),
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
                            ->options([
                                'amber' => 'Янтарный',
                                'red' => 'Красный',
                                'green' => 'Зеленый',
                                'slate' => 'Серо-сланцевый',
                                'sky' => 'Небесно-голубой',
                                'violet' => 'Фиолетовый',
                            ]),
                    ]),
                Section::make('Серверная корзина (доставка)')
                    ->description(
                        'Эти три переключателя не связаны с полем «Теги» выше: теги нужны сайту (фильтры, бейджи). Здесь — только то, как бэкенд считает корзину и заказ.'
                    )
                    ->schema([
                        Toggle::make('cart_rule_counts_as_roll')
                            ->label('1. Считать в промо «комплект к роллам»')
                            ->helperText(
                                'Вкл.: каждая единица этого товара увеличивает счётчик «роллов». Когда набралось N единиц (настройка в «Магазин → Правила корзины»), в корзину автоматически добавляется товар из пункта 2. Выкл.: для соусов, напитков, доставки и т.п.'
                            )
                            ->default(false),
                        Toggle::make('cart_rule_is_complement_set')
                            ->label('2. Это товар «комплект», который подставляет сервер')
                            ->helperText(
                                'Вкл.: этот товар системно подставляется как строка «комплект» (соус/имбирь и т.д.). Можно отметить несколько товаров — сервер добавит все отмеченные позиции. Выкл.: у обычных блюд, которые клиент заказывает вручную.'
                            )
                            ->default(false),
                        Toggle::make('cart_rule_gift_candidate')
                            ->label('3. Можно выбрать бесплатным подарком при сумме заказа')
                            ->helperText(
                                'Вкл.: товар попадает в список подарков, когда сумма корзины ≥ порога (там же в «Правила корзины»). Клиент выбирает подарок в черновике чекаута. Выкл.: обычная оплачиваемая позиция.'
                            )
                            ->default(false),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
