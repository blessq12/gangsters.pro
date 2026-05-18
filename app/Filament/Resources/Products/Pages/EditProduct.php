<?php

namespace App\Filament\Resources\Products\Pages;

use App\Domain\Product\Entity\Product as ProductEntity;
use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Редактирование товара';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->label('В архив')
                ->icon(Heroicon::OutlinedArchiveBox)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(
                    fn (): bool => $this->getRecord()->status === ProductEntity::STATUS_ACTIVE,
                )
                ->action(function (): void {
                    $this->getRecord()->update([
                        'status' => ProductEntity::STATUS_ARCHIVED,
                    ]);
                    $this->refreshFormData(['status']);
                }),
            Action::make('activate')
                ->label('На витрину')
                ->icon(Heroicon::OutlinedArrowUturnLeft)
                ->color('success')
                ->requiresConfirmation()
                ->visible(
                    fn (): bool => $this->getRecord()->status === ProductEntity::STATUS_ARCHIVED,
                )
                ->action(function (): void {
                    $this->getRecord()->update([
                        'status' => ProductEntity::STATUS_ACTIVE,
                    ]);
                    $this->refreshFormData(['status']);
                }),
            DeleteAction::make(),
        ];
    }
}
