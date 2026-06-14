<?php

namespace App\Filament\Catalog\Concerns;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

trait RendersCatalogResourceTabs
{
    #[Url(as: 'tab')]
    public ?string $activeCatalogEditTab = null;

    protected function ensureDefaultCatalogEditTab(string $defaultTab, array $validTabs = []): void
    {
        $candidate = $this->activeCatalogEditTab;

        $tabFromQuery = request()->query('tab');

        if (is_string($tabFromQuery) && $tabFromQuery !== '') {
            $candidate = $tabFromQuery;
        }

        if (
            blank($candidate)
            || str_contains($candidate, '::tab')
            || ($validTabs !== [] && ! in_array($candidate, $validTabs, true))
        ) {
            $candidate = $defaultTab;
        }

        $this->activeCatalogEditTab = $candidate;
    }

    protected function catalogEditTabs(Tabs $tabs): Tabs
    {
        return $this->configureCatalogEditTabs($tabs)
            ->livewireProperty('activeCatalogEditTab');
    }
    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel($this->hasInlineLabels())
            ->model($this->getRecord())
            ->operation('edit')
            ->statePath('data');
    }

    public function hasFormWrapper(): bool
    {
        return false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form'),
            $this->getFormActionsContentComponent(),
        ]);
    }

    protected function configureCatalogEditTabs(Tabs $tabs): Tabs
    {
        return $tabs->liberatedFromContainerGrid();
    }

    /**
     * @param  class-string<RelationManager>  $managerClass
     */
    protected function relationManagerTab(string $managerClass): Livewire
    {
        return Livewire::make(
            $managerClass,
            [
                'ownerRecord' => $this->getRecord(),
                'pageClass' => static::class,
            ],
        )->key($managerClass);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }
}
