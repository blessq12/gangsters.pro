<?php

namespace App\Filament\Promotion\Resources\PromotionPolicyResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class PromotionPolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Tabs::make('promotion-context')
                    ->columnSpanFull()
                    ->tabs([
                        'gift' => Tab::make('gift')
                            ->label('Подарок')
                            ->icon(Heroicon::OutlinedGift)
                            ->schema(self::giftSchema()),
                        'delivery' => Tab::make('delivery')
                            ->label('Доставка')
                            ->icon(Heroicon::OutlinedTruck)
                            ->schema(self::deliverySchema()),
                        'complement' => Tab::make('complement')
                            ->label('Комплект')
                            ->icon(Heroicon::OutlinedSquaresPlus)
                            ->schema(self::complementSchema()),
                    ]),
            ]);
    }

    /**
     * @return list<Component>
     */
    private static function giftSchema(): array
    {
        return [
            Section::make('Ролл в подарок')
                ->columnSpanFull()
                ->description('Кандидаты на подарок помечаются в каталоге (поле «Кандидат на подарок»). Подарок доступен при сумме корзины строго выше порога.')
                ->columns(2)
                ->schema([
                    Toggle::make('gift_benefit_active')
                        ->label('Правило подарка активно')
                        ->columnSpanFull(),
                    self::moneyInput('gift_pickup_min_order_kopecks', 'Порог при самовывозе'),
                    self::moneyInput('gift_courier_min_order_kopecks', 'Порог при доставке'),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function deliverySchema(): array
    {
        return [
            Section::make('Стоимость доставки от суммы заказа')
                ->columnSpanFull()
                ->description('Базовый тариф и зона доставки настраиваются в разделе «Доставка». От порога суммы: бесплатно в зоне, вне зоны — базовый тариф плюс надбавка.')
                ->columns(2)
                ->schema([
                    Toggle::make('delivery_benefit_active')
                        ->label('Политика доставки активна')
                        ->columnSpanFull(),
                    self::moneyInput('delivery_free_threshold_kopecks', 'Порог бесплатной доставки в зоне'),
                    self::moneyInput('delivery_outside_zone_surcharge_kopecks', 'Надбавка вне зоны при сумме от порога'),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function complementSchema(): array
    {
        return [
            Section::make('Комплект дополнений')
                ->columnSpanFull()
                ->description('Роллы помечаются в каталоге («Считается как ролл»). Наборы дополнений — («Набор дополнений»). За каждые N роллов в корзине клиенту добавляется один комплект.')
                ->columns(2)
                ->schema([
                    Toggle::make('complement_set_benefit_active')
                        ->label('Правило комплекта активно')
                        ->columnSpanFull(),
                    TextInput::make('complement_set_rolls_per_set')
                        ->label('Роллов на один комплект')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        ->default(2),
                ]),
        ];
    }

    private static function moneyInput(string $field, string $label): TextInput
    {
        return TextInput::make($field)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->required()
            ->suffix('₽')
            ->formatStateUsing(static function (mixed $state): mixed {
                if ($state === null || $state === '') {
                    return null;
                }

                return ((int) $state) / 100;
            })
            ->dehydrateStateUsing(static function (mixed $state): ?int {
                if ($state === null || $state === '') {
                    return null;
                }

                return (int) round(((float) $state) * 100);
            });
    }
}
