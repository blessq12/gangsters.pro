<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Concerns\NormalizesCompanySettingsData;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    use NormalizesCompanySettingsData;

    protected static string $resource = CompanyResource::class;

    protected static ?string $title = 'Компания';

    /** @var array<string, mixed> */
    protected array $pendingLegalData = [];

    /** @var array<string, array<string, mixed>> */
    protected array $pendingDocumentsData = [];

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $legal = $record->legal()->firstOrCreate(
            ['company_id' => $record->getKey()],
            $this->defaultLegalAttributes()
        );

        $data['legal'] = $legal->toArray();
        $data['documents'] = $this->buildDocumentsFormState();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->normalizeCompanyPhones($data);

        $this->pendingLegalData = is_array($data['legal'] ?? null) ? $data['legal'] : [];
        $this->pendingDocumentsData = is_array($data['documents'] ?? null) ? $data['documents'] : [];

        $data = $this->stripNestedCompanySettings($data);
        $data = $this->syncLegacyWorkHoursFromSchedule($data);

        return $data;
    }

    protected function afterSave(): void
    {
        $companyId = (int) $this->getRecord()->getKey();

        $this->persistCompanyLegal($companyId, $this->pendingLegalData);
        $this->persistCompanyDocuments($this->pendingDocumentsData);
    }
}
