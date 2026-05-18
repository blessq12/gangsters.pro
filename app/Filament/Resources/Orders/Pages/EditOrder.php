<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Application\Order\Service\RecalculateOrderTotalsFromItems;
use App\Filament\Resources\Orders\Concerns\NormalizesOrderFormData;
use App\Filament\Resources\Orders\Concerns\OrderWorkflowHeaderActions;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditOrder extends EditRecord
{
    use NormalizesOrderFormData;
    use OrderWorkflowHeaderActions;

    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Редактирование заказа';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                $this->getFormContentComponent()->columnSpanFull(),
                $this->getRelationManagersContentComponent()->columnSpanFull(),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->expandOrderFormDataForFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeOrderFormData($data);
    }

    protected function afterSave(): void
    {
        app(RecalculateOrderTotalsFromItems::class)->recalculate($this->getRecord());
        $this->refreshFormData(['subtotal', 'discount_total', 'total']);
    }

    protected function getHeaderActions(): array
    {
        return $this->getOrderWorkflowHeaderActions();
    }
}
