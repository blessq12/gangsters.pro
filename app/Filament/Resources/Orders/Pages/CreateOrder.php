<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Создание заказа';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                $this->getFormContentComponent()->columnSpanFull(),
            ]);
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
}

