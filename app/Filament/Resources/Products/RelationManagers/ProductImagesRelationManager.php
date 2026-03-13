<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ProductImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            TextInput::make('thumb_path')
                ->label('Thumb path')
                ->required(),
            TextInput::make('medium_path')
                ->label('Medium path')
                ->required(),
            TextInput::make('large_path')
                ->label('Large path')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('thumb_path')
            ->columns([
                TextColumn::make('sort_order')
                    ->sortable(),
                TextColumn::make('thumb_path')
                    ->label('Thumb'),
                TextColumn::make('medium_path')
                    ->label('Medium'),
                TextColumn::make('large_path')
                    ->label('Large'),
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

