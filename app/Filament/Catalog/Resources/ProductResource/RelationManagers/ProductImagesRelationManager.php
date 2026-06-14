<?php

namespace App\Filament\Catalog\Resources\ProductResource\RelationManagers;

use App\Infrastructure\Catalog\Model\PRD_ProductImage;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'productImages';

    protected static ?string $title = 'Изображения';

    protected static string | \BackedEnum | null $icon = Heroicon::OutlinedPhoto;

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $shouldCheckPolicyExistence = false;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                FileUpload::make('path')
                    ->label('Файл')
                    ->image()
                    ->disk('public')
                    ->directory(fn (): string => 'products/'.$this->getOwnerRecord()->getKey())
                    ->visibility('public')
                    ->required()
                    ->maxSize(5120),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Изображение')
            ->pluralModelLabel('Изображения')
            ->emptyStateHeading('Изображения не найдены')
            ->emptyStateDescription('Добавьте первое изображение товара.')
            ->heading('Изображения')
            ->description('Перетаскивайте строки, чтобы изменить порядок изображений в карточке товара.')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('Превью')
                    ->disk(fn (PRD_ProductImage $record): string => $record->disk ?: 'public')
                    ->square()
                    ->imageSize(64),
                TextColumn::make('path')
                    ->label('Путь')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить изображение')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['disk'] = 'public';

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Заменить файл'),
                DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Удалить выбранные'),
            ]);
    }

    protected function canReorder(): bool
    {
        return true;
    }
}
