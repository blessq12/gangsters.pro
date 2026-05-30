<?php

namespace App\Filament\Operations\Support;

use Illuminate\Database\Eloquent\Model;

trait ResolvesOperationsEditRecord
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
