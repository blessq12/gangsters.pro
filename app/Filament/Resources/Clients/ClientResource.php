<?php

namespace App\Filament\Resources\Clients;

use App\Application\Client\Query\ClientSummaryReader;
use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Infrastructure\Client\Model\UR_Client;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use UnitEnum;

class ClientResource extends Resource
{
    protected static ?string $model = UR_Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected static ?string $navigationLabel = 'Клиенты';

    // Группа: блок пользователей
    protected static string|UnitEnum|null $navigationGroup = 'Пользователи';

    // Сортировка в навигации внутри блока пользователей
    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Имя')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->placeholder('+7 (900) 123-45-67')
                    ->mask('+7 (999) 999-99-99')
                    ->formatStateUsing(function (?string $state): ?string {
                        if ($state === null || $state === '') {
                            return $state;
                        }

                        $digits = preg_replace('/\D+/', '', $state) ?? '';
                        if (preg_match('/^7\d{10}$/', $digits) !== 1) {
                            return $state;
                        }

                        return sprintf(
                            '+7 (%s) %s-%s-%s',
                            substr($digits, 1, 3),
                            substr($digits, 4, 3),
                            substr($digits, 7, 2),
                            substr($digits, 9, 2),
                        );
                    })
                    ->dehydrateStateUsing(function (?string $state): ?string {
                        if ($state === null || $state === '') {
                            return $state;
                        }

                        $digits = preg_replace('/\D+/', '', $state) ?? '';
                        if (preg_match('/^7\d{10}$/', $digits) !== 1) {
                            return $state;
                        }

                        return sprintf(
                            '+7 (%s) %s-%s-%s',
                            substr($digits, 1, 3),
                            substr($digits, 4, 3),
                            substr($digits, 7, 2),
                            substr($digits, 9, 2),
                        );
                    })
                    ->rule('regex:/^\\+7\\s\\(\\d{3}\\)\\s\\d{3}-\\d{2}-\\d{2}$/')
                    ->validationMessages([
                        'regex' => 'Телефон должен быть в формате +7 (XXX) XXX-XX-XX.',
                    ])
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable(),
                DatePicker::make('birth_date')
                    ->label('Дата рождения')
                    ->nullable(),
                Select::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активен',
                        'blocked' => 'Заблокирован',
                    ])
                    ->required()
                    ->default('active')
                    ->columnSpanFull(),
                Toggle::make('consent_personal_data')
                    ->label('Согласие на обработку данных'),
                Toggle::make('consent_marketing')
                    ->label('Согласие на маркетинг'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('birth_date')
                    ->label('Дата рождения')
                    ->date('d.m.Y'),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                IconColumn::make('consent_personal_data')
                    ->label('Персональные данные')
                    ->boolean(),
                IconColumn::make('consent_marketing')
                    ->label('Маркетинг')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
                EditAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeCells);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Профиль клиента')
                ->columns(2)
                ->schema([
                    TextEntry::make('name')->label('Имя'),
                    TextEntry::make('phone')->label('Телефон'),
                    TextEntry::make('email')->label('Email')->placeholder('—'),
                    TextEntry::make('birth_date')->label('Дата рождения')->date('d.m.Y'),
                    TextEntry::make('status')->label('Статус'),
                    TextEntry::make('created_at')->label('Создан')->dateTime('d.m.Y H:i'),
                ]),
            Section::make('Сводка по клиенту')
                ->columns(2)
                ->schema([
                    TextEntry::make('summary.orders_count')
                        ->label('Количество заказов')
                        ->state(function (UR_Client $record): int {
                            /** @var ClientSummaryReader $summary */
                            $summary = app(ClientSummaryReader::class);

                            return (int) ($summary->getSummaryById((int) $record->id)['orders_count'] ?? 0);
                        }),
                    TextEntry::make('summary.paid_orders_count')
                        ->label('Оплаченных заказов')
                        ->state(function (UR_Client $record): int {
                            /** @var ClientSummaryReader $summary */
                            $summary = app(ClientSummaryReader::class);

                            return (int) ($summary->getSummaryById((int) $record->id)['paid_orders_count'] ?? 0);
                        }),
                    TextEntry::make('summary.orders_total')
                        ->label('Сумма заказов')
                        ->state(function (UR_Client $record): string {
                            /** @var ClientSummaryReader $summary */
                            $summary = app(ClientSummaryReader::class);
                            $totalKopecks = (int) ($summary->getSummaryById((int) $record->id)['orders_total'] ?? 0);

                            return Money::formatKopecksForAdmin($totalKopecks);
                        }),
                    TextEntry::make('summary.average_order_total')
                        ->label('Средний чек')
                        ->state(function (UR_Client $record): string {
                            /** @var ClientSummaryReader $summary */
                            $summary = app(ClientSummaryReader::class);
                            $avgKopecks = (int) ($summary->getSummaryById((int) $record->id)['average_order_total'] ?? 0);

                            return Money::formatKopecksForAdmin($avgKopecks);
                        }),
                    TextEntry::make('summary.addresses_count')
                        ->label('Количество адресов')
                        ->state(function (UR_Client $record): int {
                            /** @var ClientSummaryReader $summary */
                            $summary = app(ClientSummaryReader::class);

                            return (int) ($summary->getSummaryById((int) $record->id)['addresses_count'] ?? 0);
                        }),
                    TextEntry::make('summary.last_order_at')
                        ->label('Последний заказ')
                        ->state(function (UR_Client $record): string {
                            /** @var ClientSummaryReader $summary */
                            $summary = app(ClientSummaryReader::class);
                            $value = $summary->getSummaryById((int) $record->id)['last_order_at'] ?? null;
                            if (! is_string($value) || $value === '') {
                                return 'Нет заказов';
                            }
                            try {
                                return \Illuminate\Support\Carbon::parse($value)->format('d.m.Y H:i');
                            } catch (\Throwable $e) {
                                return 'Нет заказов';
                            }
                        }),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Clients\RelationManagers\OrdersRelationManager::class,
            \App\Filament\Resources\Clients\RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
