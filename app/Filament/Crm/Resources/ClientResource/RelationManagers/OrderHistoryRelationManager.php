<?php

namespace App\Filament\Crm\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'orderHistory';

    protected static ?string $title = 'История заказов';

    protected static string | \BackedEnum | null $icon = Heroicon::OutlinedClipboardDocumentList;

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $shouldCheckPolicyExistence = false;

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('Запись истории')
            ->pluralModelLabel('История заказов')
            ->heading('История заказов')
            ->description('Слепки заказов клиента из CRM.')
            ->defaultSort('placed_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('placed_at')
                    ->label('Оформлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('order_snapshot_summary')
                    ->label('Слепок')
                    ->state(function ($record): string {
                        $snapshot = is_array($record->order_snapshot) ? $record->order_snapshot : [];
                        $orderId = $snapshot['id'] ?? $snapshot['order_id'] ?? null;
                        $total = $snapshot['total'] ?? $snapshot['totals']['grand_total_rubles'] ?? null;

                        $parts = [];
                        if ($orderId !== null) {
                            $parts[] = 'заказ #'.$orderId;
                        }
                        if ($total !== null && is_numeric($total)) {
                            $parts[] = number_format((float) $total, 0, ',', ' ').' ₽';
                        }

                        return $parts !== [] ? implode(' · ', $parts) : 'слепок';
                    })
                    ->wrap(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('История пуста')
            ->emptyStateDescription('У клиента пока нет слепков заказов.');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
