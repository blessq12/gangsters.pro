<?php

namespace App\Filament\Client\Resources\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class ClientViewSchema
{
    public static function configure(Schema $schema, ?string $livewireTabProperty = null): Schema
    {
        $tabs = Tabs::make('client-view')
            ->tabs([
                'overview' => Tab::make('overview')
                    ->label('Профиль')
                    ->icon(Heroicon::OutlinedUser)
                    ->columns(2)
                    ->schema(self::overviewFields()),
                'addresses' => Tab::make('addresses')
                    ->label('Адреса')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->schema(self::addressFields()),
                'consents' => Tab::make('consents')
                    ->label('Согласия')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->columns(2)
                    ->schema(self::consentFields()),
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
            TextInput::make('name')
                ->label('Имя'),
            TextInput::make('phone')
                ->label('Телефон'),
            TextInput::make('email')
                ->label('Email'),
            TextInput::make('birth_date')
                ->label('Дата рождения'),
            TextInput::make('created_at')
                ->label('Зарегистрирован'),
        ];
    }

    /**
     * @return list<Repeater|TextInput>
     */
    private static function addressFields(): array
    {
        return [
            TextInput::make('addresses_count')
                ->label('Всего адресов'),
            Repeater::make('addresses')
                ->label('Адресная книга')
                ->schema([
                    TextInput::make('id')
                        ->label('ID'),
                    TextInput::make('title')
                        ->label('Название'),
                    TextInput::make('street')
                        ->label('Улица'),
                    TextInput::make('house')
                        ->label('Дом'),
                    TextInput::make('entrance')
                        ->label('Подъезд'),
                    TextInput::make('apartment')
                        ->label('Квартира'),
                    TextInput::make('comment')
                        ->label('Комментарий')
                        ->columnSpanFull(),
                    TextInput::make('is_default')
                        ->label('По умолчанию'),
                ])
                ->columns(3)
                ->addable(false)
                ->deletable(false)
                ->reorderable(false)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<TextInput>
     */
    private static function consentFields(): array
    {
        return [
            TextInput::make('consent_personal_data')
                ->label('Согласие на обработку ПДн'),
            TextInput::make('consent_marketing')
                ->label('Согласие на маркетинг'),
        ];
    }
}
