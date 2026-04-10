<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Позиции заказа';

    protected static ?string $modelLabel = 'позиция';

    protected static ?string $pluralModelLabel = 'позиции';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),
                TextInput::make('product_sku')
                    ->label('Артикул')
                    ->maxLength(255),
                TextInput::make('quantity')
                    ->label('Кол-во')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('unit_price')
                    ->label('Цена за ед., ₽')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0)
                    ->formatStateUsing(fn ($state): ?float => $state === null || $state === '' ? null : Money::kopecksToApiRubles((int) $state))
                    ->dehydrateStateUsing(fn ($state): int => Money::apiRublesToKopecks($state) ?? 0),
                TextInput::make('row_subtotal')
                    ->label('Подытог, ₽')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state): ?string => $state === null ? null : Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))),
                TextInput::make('row_discount')
                    ->label('Скидка, ₽')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->minValue(0)
                    ->formatStateUsing(fn ($state): ?float => $state === null || (int) $state === 0 ? 0.0 : Money::kopecksToApiRubles((int) $state))
                    ->dehydrateStateUsing(fn ($state): int => Money::apiRublesToKopecks($state) ?? 0),
                TextInput::make('row_total')
                    ->label('Итого, ₽')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(fn ($state): ?string => $state === null ? null : Money::formatRublesRuAdaptive(Money::kopecksToApiRubles((int) $state))),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Товар')
                    ->searchable(),
                TextColumn::make('product_sku')
                    ->label('Артикул')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Цена за ед.')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—'
                    ),
                TextColumn::make('row_subtotal')
                    ->label('Подытог')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—'
                    ),
                TextColumn::make('row_discount')
                    ->label('Скидка')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null && (int) $state !== 0
                            ? Money::formatKopecksForAdmin((int) $state)
                            : Money::formatKopecksForAdmin(0)
                    ),
                TextColumn::make('row_total')
                    ->label('Итого')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—'
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): \App\Infrastructure\Order\Model\ORD_OrderItem {
                        $data['row_subtotal'] = ($data['quantity'] ?? 0) * ($data['unit_price'] ?? 0);
                        $data['row_total'] = $data['row_subtotal'] - ($data['row_discount'] ?? 0);
                        $data['product_list_price'] = $data['unit_price'] ?? 0;
                        $data['product_final_price'] = $data['unit_price'] ?? 0;

                        return $this->getOwnerRecord()->items()->create($data);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
