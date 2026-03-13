<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ProductIngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Название')
                ->required(),
            TextInput::make('amount')
                ->label('Количество')
                ->nullable(),
            TextInput::make('unit')
                ->label('Ед. изм.')
                ->nullable(),
            Toggle::make('is_allergen')
                ->label('Аллерген'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Кол-во'),
                TextColumn::make('unit')
                    ->label('Ед. изм.'),
                IconColumn::make('is_allergen')
                    ->label('Аллерген')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
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

