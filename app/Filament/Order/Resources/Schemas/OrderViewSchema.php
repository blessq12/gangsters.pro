<?php

namespace App\Filament\Order\Resources\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class OrderViewSchema
{
    public static function configure(Schema $schema, ?string $livewireTabProperty = null): Schema
    {
        $tabs = Tabs::make('order-view')
            ->tabs([
                'overview' => Tab::make('overview')
                    ->label('Общее')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columns(2)
                    ->schema(self::overviewFields()),
                'cart' => Tab::make('cart')
                    ->label('Корзина')
                    ->icon(Heroicon::OutlinedShoppingCart)
                    ->schema(self::cartFields()),
                'client' => Tab::make('client')
                    ->label('Клиент')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->schema(self::clientFields()),
                'delivery' => Tab::make('delivery')
                    ->label('Доставка')
                    ->icon(Heroicon::OutlinedTruck)
                    ->columns(2)
                    ->schema(self::deliveryFields()),
                'payment' => Tab::make('payment')
                    ->label('Оплата')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->columns(2)
                    ->schema(self::paymentFields()),
            ]);

        if ($livewireTabProperty !== null) {
            $tabs->livewireProperty($livewireTabProperty);
        }

        return $schema
            ->columns(1)
            ->components([$tabs]);
    }

    /**
     * @return list<TextInput>
     */
    private static function overviewFields(): array
    {
        return [
            TextInput::make('id')
                ->label('Идентификатор'),
            TextInput::make('checkout_id')
                ->label('Оформление')
                ->columnSpanFull(),
            TextInput::make('status')
                ->label('Статус'),
            TextInput::make('cart_items_total')
                ->label('Сумма товаров'),
            TextInput::make('created_at')
                ->label('Создан'),
        ];
    }

    /**
     * @return list<Repeater>
     */
    private static function cartFields(): array
    {
        return [
            Repeater::make('cart_lines')
                ->label('Позиции')
                ->table([
                    TableColumn::make('ID товара')
                        ->width('5rem'),
                    TableColumn::make('Название'),
                    TableColumn::make('Кол-во')
                        ->width('5rem'),
                    TableColumn::make('Цена')
                        ->width('7rem'),
                    TableColumn::make('Сумма')
                        ->width('7rem'),
                ])
                ->schema([
                    TextInput::make('product_id'),
                    TextInput::make('product_name'),
                    TextInput::make('quantity'),
                    TextInput::make('unit_price_rubles'),
                    TextInput::make('line_total_rubles'),
                ])
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<TextInput>
     */
    private static function clientFields(): array
    {
        return [
            TextInput::make('client_kind')
                ->label('Тип клиента'),
            TextInput::make('client_id')
                ->label('ID клиента'),
            TextInput::make('client_name')
                ->label('Имя'),
            TextInput::make('client_phone')
                ->label('Телефон'),
            TextInput::make('client_email')
                ->label('Email')
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<TextInput>
     */
    private static function deliveryFields(): array
    {
        return [
            TextInput::make('delivery_method')
                ->label('Способ доставки'),
            TextInput::make('delivery_street')
                ->label('Улица'),
            TextInput::make('delivery_house')
                ->label('Дом'),
            TextInput::make('delivery_entrance')
                ->label('Подъезд'),
            TextInput::make('delivery_apartment')
                ->label('Квартира'),
            TextInput::make('delivery_scheduled_at')
                ->label('Время доставки'),
            TextInput::make('delivery_comment')
                ->label('Комментарий')
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<TextInput>
     */
    private static function paymentFields(): array
    {
        return [
            TextInput::make('payment_method')
                ->label('Способ оплаты'),
            TextInput::make('payment_change_from')
                ->label('Сдача с'),
        ];
    }
}
