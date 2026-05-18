<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Resources\Clients\ClientResource;
use App\Infrastructure\Client\Model\UR_Client;
use App\Support\Client\ClientStatusLabels;
use App\Support\Money;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Профиль клиента')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Имя'),
                        TextEntry::make('phone')->label('Телефон'),
                        TextEntry::make('email')->label('Email')->placeholder('—'),
                        TextEntry::make('birth_date')->label('Дата рождения')->date('d.m.Y')->placeholder('—'),
                        TextEntry::make('status')
                            ->label('Статус')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ClientStatusLabels::statusLabel($state))
                            ->color(fn (string $state): string => ClientStatusLabels::statusColor($state)),
                        TextEntry::make('created_at')->label('Создан')->dateTime('d.m.Y H:i'),
                        TextEntry::make('updated_at')->label('Обновлён')->dateTime('d.m.Y H:i'),
                        TextEntry::make('deleted_at')
                            ->label('Удалён')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—')
                            ->visible(fn (UR_Client $record): bool => $record->trashed()),
                    ]),
                Section::make('Согласия')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('consent_personal_data')
                            ->label('Обработка ПДн')
                            ->boolean(),
                        IconEntry::make('consent_marketing')
                            ->label('Маркетинг')
                            ->boolean(),
                    ]),
                Section::make('Сводка по клиенту')
                    ->description(
                        fn (UR_Client $record): ?string => self::summaryIsEmpty($record)
                            ? 'Нет данных в reporting — появятся после заказа или синхронизации проекции.'
                            : null,
                    )
                    ->columns(2)
                    ->schema([
                        TextEntry::make('summary.orders_count')
                            ->label('Количество заказов')
                            ->state(fn (UR_Client $record): int => (int) (ClientResource::getClientSummary($record)['orders_count'] ?? 0)),
                        TextEntry::make('summary.paid_orders_count')
                            ->label('Оплаченных заказов')
                            ->state(fn (UR_Client $record): int => (int) (ClientResource::getClientSummary($record)['paid_orders_count'] ?? 0)),
                        TextEntry::make('summary.orders_total')
                            ->label('Сумма заказов')
                            ->state(function (UR_Client $record): string {
                                $totalKopecks = (int) (ClientResource::getClientSummary($record)['orders_total'] ?? 0);

                                return Money::formatKopecksForAdmin($totalKopecks);
                            }),
                        TextEntry::make('summary.average_order_total')
                            ->label('Средний чек')
                            ->state(function (UR_Client $record): string {
                                $avgKopecks = (int) (ClientResource::getClientSummary($record)['average_order_total'] ?? 0);

                                return Money::formatKopecksForAdmin($avgKopecks);
                            }),
                        TextEntry::make('summary.addresses_count')
                            ->label('Количество адресов')
                            ->state(fn (UR_Client $record): int => (int) (ClientResource::getClientSummary($record)['addresses_count'] ?? 0)),
                        TextEntry::make('summary.last_order_at')
                            ->label('Последний заказ')
                            ->state(function (UR_Client $record): string {
                                $value = ClientResource::getClientSummary($record)['last_order_at'] ?? null;
                                if (! is_string($value) || $value === '') {
                                    return 'Нет заказов';
                                }

                                try {
                                    return \Illuminate\Support\Carbon::parse($value)->format('d.m.Y H:i');
                                } catch (\Throwable) {
                                    return 'Нет заказов';
                                }
                            }),
                    ]),
            ]);
    }

    private static function summaryIsEmpty(UR_Client $record): bool
    {
        $summary = ClientResource::getClientSummary($record);

        return ($summary['orders_count'] ?? 0) === 0
            && ($summary['addresses_count'] ?? 0) === 0;
    }
}
