<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Concerns\OrderWorkflowHeaderActions;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    use OrderWorkflowHeaderActions;

    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Просмотр заказа';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                $this->getInfolistContentComponent()->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            ...$this->getOrderWorkflowHeaderActions(),
        ];
    }
}
