<?php

namespace App\Filament\Order\Resources\Pages;

use App\Filament\Order\Resources\OrderResource;
use App\Filament\Order\Resources\Schemas\OrderViewSchema;
use App\Filament\Order\Support\OrderSnapshotReader;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Просмотр заказа';

    /** @var list<string> */
    private const VALID_TABS = [
        'overview',
        'cart',
        'client',
        'delivery',
        'payment',
    ];

    #[Url(as: 'tab')]
    public ?string $activeOrderViewTab = 'overview';

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
        return OrderViewSchema::configure($schema, 'activeOrderViewTab');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return OrderSnapshotReader::formDataFromRecord($this->getRecord());
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
        $candidate = $this->activeOrderViewTab;
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

        $this->activeOrderViewTab = $candidate;
    }
}
