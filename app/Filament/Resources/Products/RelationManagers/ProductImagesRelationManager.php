<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
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

    protected static ?string $title = 'Изображения товара';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Изображения товара';
    }

    public static function getModelLabel(): string
    {
        return 'Изображение товара';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Изображения товара';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('thumb_path')
                ->label('Изображение')
                ->image()
                ->disk('media')
                ->directory('products')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Изображения товара')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordTitleAttribute('thumb_path')
            ->columns([
                ImageColumn::make('thumb_path')
                    ->label('Изображение')
                    ->disk('media')
                    ->square(),
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

