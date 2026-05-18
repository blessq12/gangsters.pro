<?php

namespace App\Filament\Resources\Banners\Tables;

use App\Filament\Resources\Banners\BannerResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class BannersTable
{
    /**
     * @param  class-string  $resourceClass
     */
    public static function configure(Table $table, string $resourceClass = BannerResource::class): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_mobile')
                    ->label('Мобилка')
                    ->disk('media')
                    ->square(),
                Tables\Columns\ImageColumn::make('image_desktop')
                    ->label('Десктоп')
                    ->disk('media')
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn ($record): string => $resourceClass::getUrl('edit', ['record' => $record])),
            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
