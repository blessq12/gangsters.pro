<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('eatsId')
                    ->label('ID заказа (внешний)'),
                TextInput::make('restaurantId')
                    ->label('ID ресторана'),
                TextInput::make('user_id')
                    ->label('ID пользователя')
                    ->numeric(),
                TextInput::make('name')
                    ->label('Имя клиента'),
                TextInput::make('tel')
                    ->label('Телефон')
                    ->tel(),
                TextInput::make('street')
                    ->label('Улица'),
                TextInput::make('house')
                    ->label('Дом'),
                TextInput::make('building')
                    ->label('Корпус'),
                TextInput::make('staircase')
                    ->label('Подъезд'),
                TextInput::make('floor')
                    ->label('Этаж'),
                TextInput::make('apartment')
                    ->label('Квартира'),
                Textarea::make('deliveryAddress')
                    ->label('Адрес доставки (строка)')
                    ->columnSpanFull(),
                TextInput::make('full_address')
                    ->label('Полный адрес'),
                TextInput::make('latitude')
                    ->label('Широта')
                    ->numeric(),
                TextInput::make('longitude')
                    ->label('Долгота')
                    ->numeric(),
                DateTimePicker::make('deliveryDate')
                    ->label('Дата/время доставки'),
                TextInput::make('deliveryType')
                    ->label('Тип доставки'),
                TextInput::make('total')
                    ->label('Сумма заказа')
                    ->numeric(),
                TextInput::make('itemsCost')
                    ->label('Сумма товаров')
                    ->numeric(),
                TextInput::make('deliveryFee')
                    ->label('Доставка')
                    ->numeric(),
                TextInput::make('change')
                    ->label('Сдача с')
                    ->numeric(),
                Textarea::make('promos')
                    ->label('Промокоды / акции')
                    ->columnSpanFull(),
                Toggle::make('delivery')
                    ->label('Доставка')
                    ->required(),
                TextInput::make('comment')
                    ->label('Комментарий'),
                TextInput::make('personQty')
                    ->label('Кол-во персон')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('payType')
                    ->label('Тип оплаты')
                    ->required()
                    ->default('cash'),
                TextInput::make('status')
                    ->label('Статус')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('frontpad_id')
                    ->label('ID во Frontpad'),
                TextInput::make('discriminator')
                    ->label('Дискриминатор'),
            ]);
    }
}
