<?php

namespace App\Filament\Catalog\Support;

use Illuminate\Database\Eloquent\Model;

trait ResolvesCatalogEditRecord
{
    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->withoutRelations();
    }

    protected function refreshRecordFormState(): void
    {
        $this->record = $this->getRecord()->refresh()->withoutRelations();

        $this->fillForm();
    }
}
