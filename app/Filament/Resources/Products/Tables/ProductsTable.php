<?php

namespace App\Filament\Resources\Products\Tables;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Filament\Resources\Products\ProductResource;
use App\Support\Money;
use App\Support\Product\ProductStatusLabels;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductsTable
{
    /**
     * @param  class-string  $editResource
     */
    public static function configure(Table $table, string $editResource = ProductResource::class): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query->with([
                    'images' => fn (HasMany $images) => $images->orderBy('sort_order')->limit(1),
                    'tags',
                    'categories',
                ]),
            )
            ->columns([
                ImageColumn::make('preview')
                    ->label('')
                    ->disk('media')
                    ->getStateUsing(
                        fn ($record): ?string => $record->images->first()?->thumb_path,
                    )
                    ->square()
                    ->size(48),
                TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('articul')
                    ->label('Артикул')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label('Цена')
                    ->sortable()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—',
                    ),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => ProductStatusLabels::label($state))
                    ->color(fn (string $state): string => ProductStatusLabels::color($state)),
                TextColumn::make('categories.name')
                    ->label('Категории')
                    ->listWithLineBreaks()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('tags.label')
                    ->label('Теги')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('cart_rule_counts_as_roll')
                    ->label('Р')
                    ->boolean()
                    ->tooltip('Считается в промо «комплект к роллам»')
                    ->toggleable(),
                IconColumn::make('cart_rule_is_complement_set')
                    ->label('К')
                    ->boolean()
                    ->tooltip('Товар «комплект» для сервера')
                    ->toggleable(),
                IconColumn::make('cart_rule_gift_candidate')
                    ->label('П')
                    ->boolean()
                    ->tooltip('Кандидат в подарок')
                    ->toggleable(),
                TextColumn::make('calories')
                    ->label('Ккал')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('slug')
                    ->label('ЧПУ')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('archived_at')
                    ->label('Архив с')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Обновлён')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        ProductEntity::STATUS_ACTIVE => ProductStatusLabels::label(ProductEntity::STATUS_ACTIVE),
                        ProductEntity::STATUS_ARCHIVED => ProductStatusLabels::label(ProductEntity::STATUS_ARCHIVED),
                    ]),
                SelectFilter::make('categories')
                    ->label('Категория')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('tags')
                    ->label('Тег')
                    ->relationship('tags', 'label')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('cart_rule_counts_as_roll')
                    ->label('Считается роллом')
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
                TernaryFilter::make('cart_rule_is_complement_set')
                    ->label('Комплект сервера')
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
                TernaryFilter::make('cart_rule_gift_candidate')
                    ->label('Подарок')
                    ->trueLabel('Да')
                    ->falseLabel('Нет'),
                Filter::make('without_category')
                    ->label('Без категории')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('categories')),
                Filter::make('without_price')
                    ->label('Без цены')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('price')),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn ($record): string => $editResource::getUrl('edit', ['record' => $record])),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('archive')
                        ->label('В архив')
                        ->icon('heroicon-o-archive-box')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(
                                fn ($record) => $record->update([
                                    'status' => ProductEntity::STATUS_ARCHIVED,
                                ]),
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('activate')
                        ->label('На витрину')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(
                                fn ($record) => $record->update([
                                    'status' => ProductEntity::STATUS_ACTIVE,
                                ]),
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
