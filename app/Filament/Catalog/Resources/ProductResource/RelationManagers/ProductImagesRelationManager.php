<?php

namespace App\Filament\Catalog\Resources\ProductResource\RelationManagers;

use App\Filament\Catalog\Forms\Components\FocalPointPicker;
use App\Infrastructure\Catalog\Model\PRD_ProductImage;
use App\Infrastructure\Catalog\Support\CatalogStoredImagePath;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
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
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->panelLayout('compact')
                    ->previewable(false)
                    ->live()
                    ->required()
                    ->maxSize(5120),
                Hidden::make('focus_x'),
                Hidden::make('focus_y'),
                FocalPointPicker::make('focus_picker')
                    ->label('Фокус')
                    ->imageField('path')
                    ->focusXField('focus_x')
                    ->focusYField('focus_y')
                    ->imageDisk('public'),
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
                    ->extraImgAttributes(fn (PRD_ProductImage $record): array => [
                        'class' => 'rounded-md object-cover bg-zinc-100 dark:bg-zinc-800',
                        'style' => $this->previewObjectPositionStyle($record),
                    ]),
                TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->badge()
                    ->sortable(),
                TextColumn::make('focus_x')
                    ->label('Фокус')
                    ->badge()
                    ->getStateUsing(function (PRD_ProductImage $record): string {
                        if ($record->focus_x === null || $record->focus_y === null) {
                            return 'центр';
                        }

                        return rtrim(rtrim(number_format((float) $record->focus_x, 2, '.', ''), '0'), '.').'% × '
                            .rtrim(rtrim(number_format((float) $record->focus_y, 2, '.', ''), '0'), '.').'%';
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Добавить изображение')
                    ->mutateFormDataUsing(fn (array $data): array => $this->normalizeImageFormData($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Изменить')
                    ->mutateFormDataUsing(fn (array $data): array => $this->normalizeImageFormData($data)),
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeImageFormData(array $data): array
    {
        $data['disk'] = 'public';
        $data['path'] = CatalogStoredImagePath::normalize($data['path'] ?? null);
        unset($data['focus_picker']);

        $x = $data['focus_x'] ?? null;
        $y = $data['focus_y'] ?? null;

        if ($x === null || $x === '' || $y === null || $y === '') {
            $data['focus_x'] = null;
            $data['focus_y'] = null;

            return $data;
        }

        $focusX = (float) $x;
        $focusY = (float) $y;

        if ($focusX < 0.0 || $focusX > 100.0 || $focusY < 0.0 || $focusY > 100.0) {
            $data['focus_x'] = null;
            $data['focus_y'] = null;

            return $data;
        }

        $data['focus_x'] = round($focusX, 2);
        $data['focus_y'] = round($focusY, 2);

        return $data;
    }

    private function previewObjectPositionStyle(PRD_ProductImage $record): string
    {
        if ($record->focus_x === null || $record->focus_y === null) {
            return 'object-position: 50% 50%;';
        }

        $x = rtrim(rtrim(number_format((float) $record->focus_x, 2, '.', ''), '0'), '.');
        $y = rtrim(rtrim(number_format((float) $record->focus_y, 2, '.', ''), '0'), '.');

        return "object-position: {$x}% {$y}%;";
    }
}
