<?php

namespace App\Filament\Content\Company\Resources\CompanyResource\Pages;

use App\Domain\Content\Repository\CompanyRepository;
use App\Filament\Content\Company\Resources\CompanyResource;
use App\Filament\Content\Company\Resources\CompanyResource\Schemas\CompanyForm;
use App\Infrastructure\Content\Model\CMP_Company;
use App\Infrastructure\Content\Model\CMP_CompanyLegal;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class ManageCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected static ?string $title = 'Компания';

    protected static ?string $navigationLabel = 'Компания';

    /** @var array<string, mixed> */
    private array $legalPayload = [];

    public function mount(int|string $record = CompanyRepository::SINGLETON_ID): void
    {
        CMP_Company::query()->firstOrCreate(
            ['id' => CompanyRepository::SINGLETON_ID],
            [
                'name' => 'Gangsters',
                'work_schedule' => $this->defaultWorkSchedule(),
            ],
        );

        $this->ensureLegalRecord();

        parent::mount($record);
    }

    public function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $legal = CMP_CompanyLegal::query()
            ->where('company_id', CompanyRepository::SINGLETON_ID)
            ->first();

        if ($legal instanceof CMP_CompanyLegal) {
            foreach (CompanyForm::legalFieldNames() as $field) {
                $data['legal_'.$field] = $legal->{$field};
            }
        }

        if (! is_array($data['work_schedule'] ?? null) || $data['work_schedule'] === []) {
            $data['work_schedule'] = $this->defaultWorkSchedule();
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->legalPayload = $this->extractLegalPayload($data);

        return array_intersect_key(
            $data,
            array_flip(CompanyForm::companyFieldNames()),
        );
    }

    protected function afterSave(): void
    {
        CMP_CompanyLegal::query()->updateOrCreate(
            ['company_id' => CompanyRepository::SINGLETON_ID],
            $this->legalPayload,
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save'),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Данные компании сохранены');
    }

    private function ensureLegalRecord(): void
    {
        CMP_CompanyLegal::query()->firstOrCreate(
            ['company_id' => CompanyRepository::SINGLETON_ID],
            [],
        );
    }

    /**
     * @return list<array{day: string, work: string, is_day_off: bool}>
     */
    private function defaultWorkSchedule(): array
    {
        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        return array_map(
            static fn (string $day) => [
                'day' => $day,
                'work' => $day === 'sun' ? '' : '10:00–22:00',
                'is_day_off' => $day === 'sun',
            ],
            $days,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extractLegalPayload(array $data): array
    {
        $payload = [];

        foreach (CompanyForm::legalFieldNames() as $field) {
            $formKey = 'legal_'.$field;
            if (! array_key_exists($formKey, $data)) {
                continue;
            }

            $payload[$field] = $data[$formKey];
        }

        return $payload;
    }
}
