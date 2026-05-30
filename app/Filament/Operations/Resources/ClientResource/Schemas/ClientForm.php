<?php

namespace App\Filament\Operations\Resources\ClientResource\Schemas;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Command\DeleteAdminClientAddressUseCase;
use App\Application\Operations\Client\DTO\AdminDeleteClientAddressDTO;
use App\Filament\Operations\Resources\OrderResource;
use App\Support\Client\ClientStatusLabels;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Hidden::make('id'),
                Section::make('Профиль')
                    ->schema([
                        TextInput::make('name')->label('Имя')->maxLength(255)->required(),
                        TextInput::make('phone')->label('Телефон')->disabled()->dehydrated(false),
                        TextInput::make('email')->label('Email')->email()->maxLength(255),
                        TextInput::make('status')
                            ->label('Статус')
                            ->formatStateUsing(fn (?string $state): string => ClientStatusLabels::statusLabel($state ?? ''))
                            ->disabled()
                            ->dehydrated(false),
                        DatePicker::make('birth_date')->label('Дата рождения'),
                        TextInput::make('created_at')
                            ->label('Регистрация')
                            ->disabled()
                            ->dehydrated(false),
                        Toggle::make('consent_personal_data')->label('Согласие на ПДн'),
                        Toggle::make('consent_marketing')->label('Маркетинг'),
                    ])
                    ->columns(2),
                Section::make('Адреса')
                    ->schema([
                        Repeater::make('addresses')
                            ->label('')
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('type')->label('Тип')->disabled()->dehydrated(false),
                                TextInput::make('title')->label('Название')->disabled()->dehydrated(false),
                                TextInput::make('street')->label('Улица')->disabled()->dehydrated(false),
                                TextInput::make('house')->label('Дом')->disabled()->dehydrated(false),
                                TextInput::make('entrance')->label('Подъезд')->disabled()->dehydrated(false),
                                TextInput::make('apartment')->label('Квартира')->disabled()->dehydrated(false),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->reorderable(false)
                            ->deletable(true)
                            ->dehydrated(false)
                            ->deleteAction(fn (Action $action): Action => $action->action(function (array $arguments, Repeater $component): void {
                                $items = $component->getRawState();
                                $addressId = (int) ($items[$arguments['item']]['id'] ?? 0);
                                $clientId = (int) ($component->getLivewire()->data['id'] ?? 0);

                                if ($clientId <= 0 || $addressId <= 0) {
                                    return;
                                }

                                try {
                                    app(DeleteAdminClientAddressUseCase::class)->execute(
                                        new AdminDeleteClientAddressDTO($clientId, $addressId),
                                    );
                                    Notification::make()->title('Адрес удалён')->success()->send();
                                    $component->getLivewire()->dispatch('refreshClientForm');
                                } catch (ApiException $exception) {
                                    Notification::make()->title($exception->getMessage())->danger()->send();
                                }
                            })),
                    ]),
                Section::make('Заказы клиента')
                    ->schema([
                        Repeater::make('orders')
                            ->label('')
                            ->schema([
                                TextInput::make('id')
                                    ->label('ID')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffixAction(
                                        Action::make('open_order')
                                            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                                            ->url(fn (?string $state): string => OrderResource::getUrl('edit', ['record' => $state ?? '']))
                                            ->openUrlInNewTab(),
                                    ),
                                TextInput::make('status_label')->label('Статус')->disabled()->dehydrated(false),
                                TextInput::make('total')->label('Сумма, ₽')->disabled()->dehydrated(false),
                                TextInput::make('created_at')->label('Создан')->disabled()->dehydrated(false),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
