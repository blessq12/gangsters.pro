<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditOrder extends EditRecord
{
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
        $addr = $data['customer_address'] ?? null;
        if (is_array($addr)) {
            $data['customer_address_street'] = $addr['street'] ?? null;
            $data['customer_address_house'] = $addr['house'] ?? null;
            $data['customer_address_entrance'] = $addr['entrance'] ?? null;
            $data['customer_address_apartment'] = $addr['apartment'] ?? null;
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['customer_address'] = array_filter([
            'street' => $data['customer_address_street'] ?? null,
            'house' => $data['customer_address_house'] ?? null,
            'entrance' => $data['customer_address_entrance'] ?? null,
            'apartment' => $data['customer_address_apartment'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
        unset(
            $data['customer_address_street'],
            $data['customer_address_house'],
            $data['customer_address_entrance'],
            $data['customer_address_apartment'],
        );
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
