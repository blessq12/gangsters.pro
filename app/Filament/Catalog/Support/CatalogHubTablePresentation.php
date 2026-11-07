<?php

namespace App\Filament\Catalog\Support;

use App\Domain\Catalog\Enum\ProductStatus;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

final class CatalogHubTablePresentation
{
    public static function catalogItemSkuColumn(): TextInputColumn
    {
        return TextInputColumn::make('sku')
            ->label('SKU')
            ->placeholder('—')
            ->searchable()
            ->rules(fn (TextInputColumn $column): array => [
                'nullable',
                'string',
                'max:128',
                Rule::unique('PRD_products', 'sku')->ignore($column->getRecord()?->getKey()),
            ])
            ->updateStateUsing(function (mixed $state, Model $record): ?string {
                $sku = is_string($state) ? trim($state) : '';
                $sku = $sku !== '' ? $sku : null;
                $record->update(['sku' => $sku]);

                return $sku;
            });
    }

    public static function catalogItemPriceColumn(): TextInputColumn
    {
        return TextInputColumn::make('price')
            ->label('Цена, ₽')
            ->type('number')
            ->placeholder('—')
            ->sortable()
            ->rules(['nullable', 'integer', 'min:0'])
            ->updateStateUsing(function (mixed $state, Model $record): ?int {
                if ($state === null || $state === '') {
                    $record->update(['price' => null]);

                    return null;
                }

                $price = (int) $state;
                $record->update(['price' => $price]);

                return $price;
            });
    }

    public static function catalogItemOldPriceColumn(): TextInputColumn
    {
        return TextInputColumn::make('old_price')
            ->label('Старая цена, ₽')
            ->type('number')
            ->placeholder('—')
            ->sortable()
            ->rules(['nullable', 'integer', 'min:0'])
            ->updateStateUsing(function (mixed $state, Model $record): ?int {
                if ($state === null || $state === '') {
                    $record->update(['old_price' => null]);

                    return null;
                }

                $oldPrice = (int) $state;
                $record->update(['old_price' => $oldPrice]);

                return $oldPrice;
            });
    }

    public static function catalogItemWeightColumn(): TextInputColumn
    {
        return TextInputColumn::make('weight')
            ->label('Вес, г')
            ->type('number')
            ->placeholder('—')
            ->sortable()
            ->rules(['nullable', 'integer', 'min:0'])
            ->updateStateUsing(function (mixed $state, Model $record): ?int {
                if ($state === null || $state === '') {
                    $record->update(['weight' => null]);

                    return null;
                }

                $weight = (int) $state;
                $record->update(['weight' => $weight > 0 ? $weight : null]);

                return $weight > 0 ? $weight : null;
            });
    }

    public static function categoryStatusColumn(): ToggleColumn
    {
        return ToggleColumn::make('is_active')
            ->label('Активна')
            ->onColor('success')
            ->offColor('gray')
            ->updateStateUsing(function (mixed $state, Model $record): bool {
                $isActive = (bool) $state;
                $record->update(['is_active' => $isActive]);

                return $isActive;
            });
    }

    public static function categoryAccompanyingColumn(): ToggleColumn
    {
        return ToggleColumn::make('is_accompanying')
            ->label('Сопутствующее')
            ->onColor('success')
            ->offColor('gray')
            ->updateStateUsing(function (mixed $state, Model $record): bool {
                $isAccompanying = (bool) $state;
                $record->update(['is_accompanying' => $isAccompanying]);

                return $isAccompanying;
            });
    }

    public static function tagStatusColumn(): TextColumn
    {
        return TextColumn::make('is_active')
            ->label('Статус')
            ->badge()
            ->formatStateUsing(
                fn (bool $state): string => $state ? 'Активен' : 'Неактивен',
            )
            ->color(
                fn (bool $state): string => $state ? 'success' : 'gray',
            );
    }

    public static function catalogItemStatusColumn(): ToggleColumn
    {
        return ToggleColumn::make('status')
            ->label('Активен')
            ->onColor('success')
            ->offColor('gray')
            ->getStateUsing(
                fn (Model $record): bool => $record->getAttribute('status') === ProductStatus::Active->value,
            )
            ->updateStateUsing(function (mixed $state, Model $record): bool {
                $isActive = (bool) $state;
                $normalized = FilamentProductPersistence::normalize([
                    'status' => $isActive
                        ? ProductStatus::Active->value
                        : ProductStatus::Archived->value,
                ]);

                $record->update([
                    'status' => $normalized['status'],
                    'archived_at' => $normalized['archived_at'],
                ]);

                return $isActive;
            });
    }

    public static function categoryStatusFilter(): SelectFilter
    {
        return SelectFilter::make('is_active')
            ->label('Статус')
            ->options([
                '1' => 'Активна',
                '0' => 'Неактивна',
            ]);
    }

    public static function catalogItemStatusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Статус')
            ->options([
                ProductStatus::Active->value => 'Активен',
                ProductStatus::Archived->value => 'В архиве',
            ]);
    }

    public static function productSystemColumn(): TextColumn
    {
        return TextColumn::make('is_system')
            ->label('Системный')
            ->badge()
            ->formatStateUsing(
                fn (bool $state): string => $state ? 'Да' : 'Нет',
            )
            ->color(
                fn (bool $state): string => $state ? 'warning' : 'gray',
            );
    }

    public static function productSystemFilter(): SelectFilter
    {
        return SelectFilter::make('is_system')
            ->label('Системный товар')
            ->options([
                '1' => 'Да',
                '0' => 'Нет',
            ]);
    }

    public static function productMetaFilter(): SelectFilter
    {
        return SelectFilter::make('product_meta')
            ->label('Мета товара')
            ->options([
                'meta_counts_as_roll' => 'Считается как ролл',
                'meta_is_complement_set' => 'Набор дополнений',
            ])
            ->query(function (Builder $query, array $data): void {
                $column = $data['value'] ?? null;

                if (! is_string($column)) {
                    return;
                }

                if (! in_array($column, [
                    'meta_counts_as_roll',
                    'meta_is_complement_set',
                ], true)) {
                    return;
                }

                $query->where($column, true);
            });
    }
}
