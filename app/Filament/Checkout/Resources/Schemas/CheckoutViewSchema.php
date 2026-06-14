<?php

namespace App\Filament\Checkout\Resources\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class CheckoutViewSchema
{
    public static function configure(Schema $schema, ?string $livewireTabProperty = null): Schema
    {
        $tabs = Tabs::make('checkout-view')
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
                ->label('Идентификатор')
                ->columnSpanFull(),
            TextInput::make('status')
                ->label('Статус'),
            TextInput::make('created_at')
                ->label('Создано'),
            TextInput::make('confirmed_at')
                ->label('Подтверждено'),
        ];
    }

    /**
     * @return list<Repeater|TextInput>
     */
    private static function cartFields(): array
    {
        return [
            TextInput::make('cart_items_total')
                ->label('Сумма товаров'),
            Repeater::make('cart_lines')
                ->label('Позиции')
                ->schema([
                    TextInput::make('product_id')
                        ->label('ID товара'),
                    TextInput::make('product_name')
                        ->label('Название'),
                    TextInput::make('quantity')
                        ->label('Количество'),
                    TextInput::make('unit_price_rubles')
                        ->label('Цена за шт.'),
                    TextInput::make('line_total_rubles')
                        ->label('Сумма строки'),
                ])
                ->columns(5)
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
