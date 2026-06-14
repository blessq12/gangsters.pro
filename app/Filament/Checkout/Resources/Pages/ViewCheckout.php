<?php

namespace App\Filament\Checkout\Resources\Pages;

use App\Filament\Checkout\Resources\CheckoutResource;
use App\Filament\Checkout\Resources\Schemas\CheckoutViewSchema;
use App\Filament\Checkout\Support\CheckoutSnapshotReader;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class ViewCheckout extends ViewRecord
{
    protected static string $resource = CheckoutResource::class;

    protected static ?string $title = 'Просмотр оформления';

    /** @var list<string> */
    private const VALID_TABS = [
        'overview',
        'cart',
        'client',
        'delivery',
        'payment',
    ];

    #[Url(as: 'tab')]
    public ?string $activeCheckoutViewTab = 'overview';

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
        return CheckoutViewSchema::configure($schema, 'activeCheckoutViewTab');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return CheckoutSnapshotReader::formDataFromRecord($this->getRecord());
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
        $candidate = $this->activeCheckoutViewTab;
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

        $this->activeCheckoutViewTab = $candidate;
    }
}
