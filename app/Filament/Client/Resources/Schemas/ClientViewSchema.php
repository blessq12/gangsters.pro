<?php

namespace App\Filament\Client\Resources\Schemas;

use App\Filament\Support\FilamentRuPhoneField;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
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
            FilamentRuPhoneField::makeReadOnly('phone', 'Телефон'),
            TextInput::make('email')
                ->label('Email'),
            TextInput::make('birth_date')
                ->label('Дата рождения'),
            TextInput::make('created_at')
                ->label('Зарегистрирован'),
        ];
    }

    /**
     * @return list<RepeatableEntry>
     */
    private static function addressFields(): array
    {
        return [
            RepeatableEntry::make('addresses')
                ->label('Адресная книга')
                ->table([
                    TableColumn::make('ID')
                        ->width('4rem'),
                    TableColumn::make('Название'),
                    TableColumn::make('Улица'),
                    TableColumn::make('Дом')
                        ->width('5rem'),
                    TableColumn::make('Подъезд')
                        ->width('6rem'),
                    TableColumn::make('Квартира')
                        ->width('6rem'),
                    TableColumn::make('По умолчанию')
                        ->width('7rem'),
                    TableColumn::make('Комментарий'),
                ])
                ->schema([
                    TextEntry::make('id'),
                    TextEntry::make('title')
                        ->placeholder('—'),
                    TextEntry::make('street'),
                    TextEntry::make('house'),
                    TextEntry::make('entrance')
                        ->placeholder('—'),
                    TextEntry::make('apartment')
                        ->placeholder('—'),
                    TextEntry::make('is_default'),
                    TextEntry::make('comment')
                        ->placeholder('—'),
                ])
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
