<?php

namespace App\Filament\Operations\Resources\ClientResource\Pages;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Operations\Client\Command\AddAdminClientAddressUseCase;
use App\Application\Operations\Client\Command\BlockAdminClientUseCase;
use App\Application\Operations\Client\Command\RequestAdminClientPasswordResetUseCase;
use App\Application\Operations\Client\Command\UnblockAdminClientUseCase;
use App\Application\Operations\Client\Command\UpdateAdminClientUseCase;
use App\Application\Operations\Client\DTO\AdminAddClientAddressDTO;
use App\Filament\Support\AdminClientEditReadHelper;
use App\Domain\Client\Entity\Client;
use App\Filament\Operations\Resources\ClientResource;
use App\Filament\Operations\Support\FilamentClientFormMapper;
use App\Filament\Operations\Support\ResolvesOperationsEditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class EditClient extends EditRecord
{
    use ResolvesOperationsEditRecord;

    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Клиент';

    #[On('refreshClientForm')]
    public function refreshClientForm(): void
    {
        $this->fillForm();
    }

    protected function getHeaderActions(): array
    {
        $clientId = (int) $this->getRecord()->getKey();
        $status = (string) ($this->getRecord()->status ?? '');

        return [
            Action::make('add_address')
                ->label('Добавить адрес')
                ->icon(Heroicon::OutlinedPlus)
                ->form([
                    TextInput::make('type')->label('Тип')->required(),
                    TextInput::make('title')->label('Название'),
                    TextInput::make('street')->label('Улица')->required(),
                    TextInput::make('house')->label('Дом')->required(),
                    TextInput::make('entrance')->label('Подъезд'),
                    TextInput::make('apartment')->label('Квартира'),
                    Toggle::make('make_default')->label('Сделать основным'),
                ])
                ->action(function (array $data) use ($clientId): void {
                    try {
                        app(AddAdminClientAddressUseCase::class)->execute(new AdminAddClientAddressDTO(
                            clientId: $clientId,
                            type: (string) $data['type'],
                            title: filled($data['title'] ?? null) ? (string) $data['title'] : null,
                            street: (string) $data['street'],
                            house: (string) $data['house'],
                            entrance: filled($data['entrance'] ?? null) ? (string) $data['entrance'] : null,
                            apartment: filled($data['apartment'] ?? null) ? (string) $data['apartment'] : null,
                            makeDefault: (bool) ($data['make_default'] ?? false),
                        ));
                        Notification::make()->title('Адрес добавлен')->success()->send();
                        $this->fillForm();
                    } catch (ApiException $exception) {
                        Notification::make()->title($exception->getMessage())->danger()->send();
                    }
                }),
            Action::make('block')
                ->label('Заблокировать')
                ->color('danger')
                ->icon(Heroicon::OutlinedNoSymbol)
                ->requiresConfirmation()
                ->visible($status !== Client::STATUS_BLOCKED)
                ->action(fn () => $this->toggleBlock(true)),
            Action::make('unblock')
                ->label('Разблокировать')
                ->color('success')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->requiresConfirmation()
                ->visible($status === Client::STATUS_BLOCKED)
                ->action(fn () => $this->toggleBlock(false)),
            Action::make('reset_password')
                ->label('Сброс пароля')
                ->icon(Heroicon::OutlinedKey)
                ->requiresConfirmation()
                ->modalDescription('Клиенту будет отправлена ссылка для сброса пароля на email из профиля.')
                ->action(fn () => $this->resetPassword()),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return FilamentClientFormMapper::toFormState(
            $this->loadClientPayload((int) $this->getRecord()->getKey()),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            app(UpdateAdminClientUseCase::class)->execute(
                FilamentClientFormMapper::toUpdateDto((int) $record->getKey(), $data),
            );
            Notification::make()->title('Клиент сохранён')->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();

            throw new Halt;
        }

        return $record->refresh();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null;
    }

    private function toggleBlock(bool $block): void
    {
        $clientId = (int) $this->getRecord()->getKey();

        try {
            if ($block) {
                app(BlockAdminClientUseCase::class)->execute($clientId);
                Notification::make()->title('Клиент заблокирован')->success()->send();
            } else {
                app(UnblockAdminClientUseCase::class)->execute($clientId);
                Notification::make()->title('Клиент разблокирован')->success()->send();
            }

            $this->fillForm();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    private function resetPassword(): void
    {
        try {
            app(RequestAdminClientPasswordResetUseCase::class)->execute((int) $this->getRecord()->getKey());
            Notification::make()->title('Ссылка для сброса пароля отправлена')->success()->send();
        } catch (ApiException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadClientPayload(int $clientId): array
    {
        try {
            return app(AdminClientEditReadHelper::class)->payload($clientId);
        } catch (ApiException $exception) {
            abort(404, $exception->getMessage());
        }
    }
}
