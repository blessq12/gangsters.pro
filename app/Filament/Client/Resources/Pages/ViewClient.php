<?php

namespace App\Filament\Client\Resources\Pages;

use App\Filament\Client\Resources\ClientResource;
use App\Filament\Client\Resources\Schemas\ClientViewSchema;
use App\Filament\Client\Support\ClientProfileReader;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected static ?string $title = 'Просмотр клиента';

    /** @var list<string> */
    private const VALID_TABS = [
        'overview',
        'addresses',
        'consents',
    ];

    #[Url(as: 'tab')]
    public ?string $activeClientViewTab = 'overview';

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->ensureDefaultViewTab();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return ClientViewSchema::configure($schema, 'activeClientViewTab');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return ClientProfileReader::formDataFromRecord($this->getRecord());
    }

    /**
     * @return list<\Filament\Actions\Action>
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    private function ensureDefaultViewTab(): void
    {
        $candidate = $this->activeClientViewTab;
        $tabFromQuery = request()->query('tab');

        if (is_string($tabFromQuery) && $tabFromQuery !== '') {
            $candidate = $tabFromQuery;
        }

        if (
            blank($candidate)
            || ! in_array($candidate, self::VALID_TABS, true)
        ) {
            $candidate = 'overview';
        }

        $this->activeClientViewTab = $candidate;
    }
}
