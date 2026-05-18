<?php

namespace App\Filament\Resources\Clients\Concerns;

use App\Domain\Client\Entity\Client;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

trait ClientWorkflowHeaderActions
{
    /**
     * @return array<int, Action>
     */
    protected function getClientStatusHeaderActions(): array
    {
        return [
            Action::make('block')
                ->label('Заблокировать')
                ->icon(Heroicon::OutlinedNoSymbol)
                ->color('danger')
                ->requiresConfirmation()
                ->visible(
                    fn (): bool => $this->getRecord()->status === Client::STATUS_ACTIVE
                        && ! $this->getRecord()->trashed(),
                )
                ->action(function (): void {
                    $this->getRecord()->update(['status' => Client::STATUS_BLOCKED]);
                    $this->refreshRecord();
                    Notification::make()
                        ->title('Клиент заблокирован')
                        ->success()
                        ->send();
                }),
            Action::make('activate')
                ->label('Разблокировать')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(
                    fn (): bool => $this->getRecord()->status === Client::STATUS_BLOCKED
                        && ! $this->getRecord()->trashed(),
                )
                ->action(function (): void {
                    $this->getRecord()->update(['status' => Client::STATUS_ACTIVE]);
                    $this->refreshRecord();
                    Notification::make()
                        ->title('Клиент активирован')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<int, DeleteAction|RestoreAction|ForceDeleteAction>
     */
    protected function getClientTrashHeaderActions(): array
    {
        return [
            RestoreAction::make()
                ->visible(fn (): bool => $this->getRecord()->trashed()),
            ForceDeleteAction::make()
                ->visible(fn (): bool => $this->getRecord()->trashed()),
            DeleteAction::make()
                ->visible(fn (): bool => ! $this->getRecord()->trashed()),
        ];
    }

    /**
     * @return array<int, Action|DeleteAction|RestoreAction|ForceDeleteAction>
     */
    protected function getClientWorkflowHeaderActions(): array
    {
        return [
            ...$this->getClientStatusHeaderActions(),
            ...$this->getClientTrashHeaderActions(),
        ];
    }
}
