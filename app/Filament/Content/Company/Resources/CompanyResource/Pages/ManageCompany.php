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
                'schedule' => $this->defaultScheduleObject(),
                'socials' => $this->emptySocials(),
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

        $socials = is_array($data['socials'] ?? null) ? $data['socials'] : [];
        foreach (CompanyForm::socialFieldKeys() as $key) {
            $data['social_'.$key] = $socials[$key] ?? null;
        }

        $data['schedule_rows'] = $this->scheduleObjectToRows($data['schedule'] ?? null);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->legalPayload = $this->extractLegalPayload($data);

        $schedule = array_key_exists('schedule_rows', $data)
            ? $this->scheduleRowsToObject($data['schedule_rows'])
            : $this->existingScheduleOrDefault();

        return [
            'name' => $data['name'] ?? null,
            'description' => $this->nullableString($data['description'] ?? null),
            'phone' => $this->nullableString($data['phone'] ?? null),
            'email' => $this->nullableString($data['email'] ?? null),
            'socials' => $this->extractSocialsPayload($data),
            'schedule' => $schedule,
        ];
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
     * @return array<string, array{work: string|null, is_day_off: bool}>
     */
    private function defaultScheduleObject(): array
    {
        $out = [];
        foreach (CompanyForm::DAYS as $day) {
            $out[$day] = [
                'work' => $day === 'sun' ? null : '10:00–22:00',
                'is_day_off' => $day === 'sun',
            ];
        }

        return $out;
    }

    /**
     * @return array{telegram: null, vk: null, inst: null, site_url: null, whatsapp: null}
     */
    private function emptySocials(): array
    {
        return [
            'telegram' => null,
            'vk' => null,
            'inst' => null,
            'site_url' => null,
            'whatsapp' => null,
        ];
    }

    /**
     * @return list<array{day: string, day_label: string, work: string|null, is_day_off: bool}>
     */
    private function scheduleObjectToRows(mixed $schedule): array
    {
        $obj = is_array($schedule) ? $schedule : [];
        $rows = [];

        foreach (CompanyForm::DAYS as $day) {
            $cell = $obj[$day] ?? null;
            $rows[] = [
                'day' => $day,
                'day_label' => CompanyForm::DAY_LABELS[$day] ?? $day,
                'work' => is_array($cell) ? $this->nullableString($cell['work'] ?? null) : null,
                'is_day_off' => is_array($cell) ? $this->toBool($cell['is_day_off'] ?? false) : false,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, array{work: string|null, is_day_off: bool}>
     */
    private function scheduleRowsToObject(mixed $rows): array
    {
        $out = $this->existingScheduleOrDefault();
        if (! is_array($rows)) {
            return $out;
        }

        // Repeater отдаёт UUID-ключи; day в hidden иногда теряется при dehydrate.
        // Порядок строк фиксирован (add/delete/reorder выключены) → маппим по индексу.
        $indexed = array_values($rows);

        foreach (CompanyForm::DAYS as $index => $day) {
            $row = $indexed[$index] ?? null;
            if (! is_array($row)) {
                continue;
            }

            $dayFromRow = strtolower(trim((string) ($row['day'] ?? '')));
            if ($dayFromRow !== '' && in_array($dayFromRow, CompanyForm::DAYS, true)) {
                $day = $dayFromRow;
            }

            $out[$day] = [
                'work' => $this->nullableString($row['work'] ?? null),
                'is_day_off' => $this->toBool($row['is_day_off'] ?? false),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, array{work: string|null, is_day_off: bool}>
     */
    private function existingScheduleOrDefault(): array
    {
        $record = $this->record ?? null;
        if ($record instanceof CMP_Company && is_array($record->schedule) && $record->schedule !== []) {
            return $this->scheduleRowsToObjectFromStored($record->schedule);
        }

        return $this->defaultScheduleObject();
    }

    /**
     * Нормализация уже сохранённого JSON schedule (без дефолтного затирания часов).
     *
     * @param  array<string, mixed>  $schedule
     * @return array<string, array{work: string|null, is_day_off: bool}>
     */
    private function scheduleRowsToObjectFromStored(array $schedule): array
    {
        $out = $this->defaultScheduleObject();

        foreach (CompanyForm::DAYS as $day) {
            $cell = $schedule[$day] ?? null;
            if (! is_array($cell)) {
                continue;
            }

            $out[$day] = [
                'work' => $this->nullableString($cell['work'] ?? null),
                'is_day_off' => $this->toBool($cell['is_day_off'] ?? false),
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>
     */
    private function extractSocialsPayload(array $data): array
    {
        $payload = $this->emptySocials();

        foreach (CompanyForm::socialFieldKeys() as $key) {
            $payload[$key] = $this->nullableString($data['social_'.$key] ?? null);
        }

        return $payload;
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

            $payload[$field] = $this->nullableString($data[$formKey]);
        }

        return $payload;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function toBool(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
}
