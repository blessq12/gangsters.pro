<?php

namespace App\Filament\Crm\Resources\ClientResource\Pages;

use App\Filament\Crm\Resources\ClientResource;
use App\Filament\Crm\Resources\ClientResource\Schemas\ClientViewSchema;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Просмотр клиента';

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return ClientViewSchema::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $favoriteIds = $data['favorite_product_ids'] ?? [];
        if (! is_array($favoriteIds)) {
            $favoriteIds = [];
        }

        $data['favorite_product_ids_label'] = $favoriteIds === []
            ? null
            : implode(', ', array_map('strval', $favoriteIds));

        $createdAt = $data['created_at'] ?? null;
        if ($createdAt instanceof \DateTimeInterface) {
            $data['created_at'] = $createdAt->format('d.m.Y H:i');
        }

        $addresses = $data['addresses'] ?? [];
        if (! is_array($addresses)) {
            $data['addresses'] = [];
        }

        return $data;
    }

    /**
     * @return list<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
