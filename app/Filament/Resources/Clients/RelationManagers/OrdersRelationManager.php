<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Domain\Order\Enums\DeliveryMethod;
use App\Domain\Order\Enums\PaymentMethod;
use App\Domain\Order\Enums\PaymentStatus;
use App\Support\Money;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Заказы';

    protected static ?string $modelLabel = 'заказ';

    protected static ?string $pluralModelLabel = 'заказы';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID заказа')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->numeric()
                    ->formatStateUsing(
                        fn ($state): string => $state !== null
                            ? Money::formatKopecksForAdmin((int) $state)
                            : '—'
                    ),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Заглушка: новые заказы создаются не через админку клиента.
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Просмотр')
                    ->modalHeading('Данные заказа')
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        // Форматированная сумма в рублях
                        $total = (int) ($data['total'] ?? 0);
                        $data['total_rub'] = Money::formatKopecksForAdmin($total);

                        // Человекочитаемые enum'ы
                        if (isset($data['delivery_method']) && $data['delivery_method']) {
                            $data['delivery_method'] =
                                DeliveryMethod::tryFrom($data['delivery_method'])?->label()
                                ?? $data['delivery_method'];
                        }

                        if (isset($data['payment_method']) && $data['payment_method']) {
                            $data['payment_method'] =
                                PaymentMethod::tryFrom($data['payment_method'])?->label()
                                ?? $data['payment_method'];
                        }

                        if (isset($data['payment_status']) && $data['payment_status']) {
                            $data['payment_status'] =
                                PaymentStatus::tryFrom($data['payment_status'])?->label()
                                ?? $data['payment_status'];
                        }

                        if (isset($data['status']) && $data['status']) {
                            $data['status'] = match ($data['status']) {
                                'new' => 'Новый',
                                'preparing' => 'Готовится',
                                'in_transit' => 'В пути',
                                'delivered' => 'Доставлен',
                                default => $data['status'],
                            };
                        }

                        // Форматирование телефона клиента
                        if (! empty($data['customer_phone'])) {
                            $digits = preg_replace('/\D+/', '', (string) $data['customer_phone']);
                            $tail = substr($digits, -10);
                            if (strlen($tail) === 10) {
                                $p1 = substr($tail, 0, 3);
                                $p2 = substr($tail, 3, 3);
                                $p3 = substr($tail, 6, 2);
                                $p4 = substr($tail, 8, 2);
                                $data['customer_phone'] = sprintf('+7 (%s) %s-%s-%s', $p1, $p2, $p3, $p4);
                            }
                        }

                        // Форматирование даты создания
                        if (! empty($data['created_at'])) {
                            try {
                                $data['created_at'] = Carbon::parse($data['created_at'])
                                    ->format('d.m.Y H:i');
                            } catch (\Throwable $e) {
                                // оставляем как есть, если не получилось распарсить
                            }
                        }

                        // Краткое содержание позиций заказа
                        if (method_exists($record, 'items')) {
                            $items = $record->items;
                            $lines = [];
                            foreach ($items as $item) {
                                $lines[] = sprintf(
                                    '%s × %d — %s',
                                    $item->product_name,
                                    (int) $item->quantity,
                                    Money::formatKopecksForAdmin((int) $item->row_total)
                                );
                            }
                            $data['items_summary'] = implode(PHP_EOL, $lines);
                        } else {
                            $data['items_summary'] = null;
                        }

                        return $data;
                    })
                    ->schema([
                        TextInput::make('id')
                            ->label('Номер заказа')
                            ->disabled(),
                        TextInput::make('status')
                            ->label('Статус')
                            ->disabled(),
                        TextInput::make('total_rub')
                            ->label('Сумма')
                            ->disabled(),
                        TextInput::make('created_at')
                            ->label('Создан')
                            ->disabled(),
                        TextInput::make('customer_name')
                            ->label('Имя клиента')
                            ->disabled(),
                        TextInput::make('customer_phone')
                            ->label('Телефон')
                            ->disabled(),
                        TextInput::make('customer_email')
                            ->label('Email')
                            ->columnSpan(2)
                            ->disabled(),
                        Textarea::make('delivery_address')
                            ->label('Адрес доставки')
                            ->columnSpan(2)
                            ->disabled()
                            ->formatStateUsing(function ($state): ?string {
                                if ($state === null) {
                                    return null;
                                }

                                if (! is_array($state)) {
                                    return (string) $state;
                                }

                                $parts = array_filter([
                                    $state['street'] ?? null,
                                    isset($state['house']) ? 'д. '.$state['house'] : null,
                                    isset($state['entrance']) ? 'подъезд '.$state['entrance'] : null,
                                    isset($state['apartment']) ? 'кв. '.$state['apartment'] : null,
                                ]);

                                return $parts === [] ? null : implode(', ', $parts);
                            }),
                        Textarea::make('delivery_comment')
                            ->label('Комментарий к доставке')
                            ->columnSpan(2)
                            ->disabled(),
                        TextInput::make('delivery_method')
                            ->label('Способ доставки')
                            ->disabled(),
                        TextInput::make('payment_method')
                            ->label('Способ оплаты')
                            ->disabled(),
                        TextInput::make('payment_status')
                            ->label('Статус оплаты')
                            ->disabled(),
                        Textarea::make('items_summary')
                            ->label('Состав заказа')
                            ->columnSpan(2)
                            ->disabled(),
                    ]),
            ]);
    }
}
