<?php

namespace App\Filament\Resources\Orders\RelationManagers;

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
                    ->label('Цена за ед. (коп)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('row_subtotal')
                    ->label('Подытог (коп)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('row_discount')
                    ->label('Скидка (коп)')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                TextInput::make('row_total')
                    ->label('Итого (коп)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
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
                    ->sortable(),
                TextColumn::make('row_subtotal')
                    ->label('Подытог')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('row_discount')
                    ->label('Скидка')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('row_total')
                    ->label('Итого')
                    ->numeric()
                    ->sortable(),
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
