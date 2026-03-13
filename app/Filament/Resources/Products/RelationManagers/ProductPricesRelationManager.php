<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ProductPricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('amount')
                ->label('Сумма (в копейках)')
                ->numeric()
                ->required(),
            Select::make('customer_status')
                ->label('Статус клиента')
                ->options([
                    'default' => 'Обычный',
                    'vip' => 'VIP',
                ])
                ->required(),
            Toggle::make('is_default')
                ->label('По умолчанию'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB', divideBy: 100),
                TextColumn::make('customer_status')
                    ->label('Статус клиента'),
                IconColumn::make('is_default')
                    ->label('По умолчанию')
                    ->boolean(),
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

