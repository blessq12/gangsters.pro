<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\Concerns\NormalizesOrderFormData;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateOrder extends CreateRecord
{
    use NormalizesOrderFormData;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeOrderFormData($data);
    }
}
