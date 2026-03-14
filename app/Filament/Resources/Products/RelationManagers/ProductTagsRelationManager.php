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

class ProductTagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    protected static ?string $title = 'Теги';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Теги';
    }

    public static function getModelLabel(): string
    {
        return 'Тег';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Теги';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->label('Код тега')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Теги')
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->label('Код')
                    ->searchable(),
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

