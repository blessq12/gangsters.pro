<?php

namespace App\Filament\Catalog\Support;

use App\Filament\Catalog\Resources\CategoryResource;
use App\Filament\Catalog\Resources\ProductResource;
use App\Filament\Catalog\Resources\ProductSetResource;
use App\Filament\Catalog\Resources\TagResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class CatalogHubTableActions
{
    /**
     * @param  class-string  $resourceClass
     */
    public static function apply(Table $table, string $resourceClass): Table
    {
        return $table
            ->modelLabel($resourceClass::getModelLabel())
            ->pluralModelLabel($resourceClass::getPluralModelLabel())
            ->emptyStateHeading(self::emptyStateHeading($resourceClass))
            ->emptyStateDescription(self::emptyStateDescription($resourceClass))
            ->headerActions([
                Action::make('create')
                    ->label('Создать')
                    ->icon(Heroicon::Plus)
                    ->url(fn (): string => $resourceClass::getUrl('create')),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Model $record): string => $resourceClass::getUrl('edit', ['record' => $record])),
                DeleteAction::make(),
            ]);
    }

    public static function forCategories(Table $table): Table
    {
        return self::apply($table, CategoryResource::class);
    }

    public static function forProducts(Table $table): Table
    {
        return self::apply($table, ProductResource::class);
    }

    public static function forSets(Table $table): Table
    {
        return self::apply($table, ProductSetResource::class);
    }

    public static function forTags(Table $table): Table
    {
        return self::apply($table, TagResource::class);
    }

    /**
     * @param  class-string  $resourceClass
     */
    private static function emptyStateHeading(string $resourceClass): string
    {
        return match ($resourceClass) {
            CategoryResource::class => 'Категории не найдены',
            ProductResource::class => 'Товары не найдены',
            ProductSetResource::class => 'Наборы не найдены',
            TagResource::class => 'Теги не найдены',
            default => 'Записи не найдены',
        };
    }

    /**
     * @param  class-string  $resourceClass
     */
    private static function emptyStateDescription(string $resourceClass): string
    {
        return match ($resourceClass) {
            CategoryResource::class => 'Создайте категорию, чтобы начать.',
            ProductResource::class => 'Создайте товар, чтобы начать.',
            ProductSetResource::class => 'Создайте набор, чтобы начать.',
            TagResource::class => 'Создайте тег, чтобы начать.',
            default => 'Создайте запись, чтобы начать.',
        };
    }
}
