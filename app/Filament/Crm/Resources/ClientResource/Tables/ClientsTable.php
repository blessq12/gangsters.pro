<?php

namespace App\Filament\Crm\Resources\ClientResource\Tables;

use App\Filament\Support\FilamentRuPhoneField;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->formatStateUsing(
                        fn (?string $state): string => FilamentRuPhoneField::formatState($state) ?? '—',
                    )
                    ->searchable()
                    ->copyable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                IconColumn::make('consent_marketing')
                    ->label('Маркетинг')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('addresses_count')
                    ->label('Адреса')
                    ->state(function ($record): int {
                        $addresses = $record->addresses;

                        return is_array($addresses) ? count($addresses) : 0;
                    }),
                TextColumn::make('created_at')
                    ->label('Регистрация')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('consent_marketing')
                    ->label('Согласие на маркетинг'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр'),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('Клиенты не найдены')
            ->emptyStateDescription('Пока нет зарегистрированных клиентов CRM.');
    }
}
