<?php

namespace App\Filament\Catalog\Resources\ProductResource\RelationManagers;

use App\Infrastructure\Catalog\Model\PRD_ProductImage;
use App\Infrastructure\Catalog\Support\CatalogStoredImagePath;
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
                    ->label('Изображение')
                    ->image()
                    ->disk('public')
                    ->directory(fn (): string => 'products/'.$this->getOwnerRecord()->getKey())
                    ->visibility('public')
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->imagePreviewHeight('220')
                    ->panelLayout('integrated')
                    ->openable()
                    ->downloadable()
                    ->previewable()
                    ->required()
                    ->maxSize(5120)
                    ->helperText('JPEG, PNG или WebP. До 5 МБ.'),
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
            ->description('Превью и порядок показа в карточке. Перетаскивайте строки, чтобы изменить порядок.')
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ImageColumn::make('path')
                    ->label('Превью')
                    ->getStateUsing(
                        fn (PRD_ProductImage $record): ?string => CatalogStoredImagePath::normalize($record->path),
                    )
                    ->disk(fn (PRD_ProductImage $record): string => $record->disk ?: 'public')
                    ->visibility('public')
                    ->checkFileExistence(false)
                    ->imageHeight(140)
                    ->extraImgAttributes([
                        'class' => 'rounded-md object-contain bg-zinc-100 dark:bg-zinc-800',
                    ]),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->badge()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить изображение')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['disk'] = 'public';
                        $data['path'] = CatalogStoredImagePath::normalize($data['path'] ?? null);

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Заменить файл')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['disk'] = 'public';
                        $data['path'] = CatalogStoredImagePath::normalize($data['path'] ?? null);

                        return $data;
                    }),
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
