<?php

namespace App\Filament\Company\Resources\CompanyResource\Pages;

use App\Domain\Company\Repository\CompanyRepository;
use App\Filament\Company\Resources\CompanyResource;
use App\Filament\Company\Resources\CompanyResource\Schemas\CompanyForm;
use App\Infrastructure\Company\Model\CMP_Company;
use App\Infrastructure\Company\Model\CMP_CompanyDocument;
use App\Infrastructure\Company\Model\CMP_CompanyLegal;
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

    /** @var array<string, array{title: string, content: string|null}> */
    private array $documentsPayload = [];

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
        $this->ensureDocuments();

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

        foreach (CompanyForm::documentDefinitions() as $key => $defaultTitle) {
            $document = CMP_CompanyDocument::query()
                ->where('company_id', CompanyRepository::SINGLETON_ID)
                ->where('key', $key)
                ->first();

            $data["document_{$key}_title"] = $document?->title ?? $defaultTitle;
            $data["document_{$key}_content"] = $document?->content;
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
        $this->documentsPayload = $this->extractDocumentsPayload($data);

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

        foreach ($this->documentsPayload as $key => $payload) {
            CMP_CompanyDocument::query()->updateOrCreate(
                [
                    'company_id' => CompanyRepository::SINGLETON_ID,
                    'key' => $key,
                ],
                [
                    'title' => $payload['title'],
                    'content' => $payload['content'],
                ],
            );
        }
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

    private function ensureDocuments(): void
    {
        foreach (CompanyForm::documentDefinitions() as $key => $title) {
            CMP_CompanyDocument::query()->firstOrCreate(
                [
                    'company_id' => CompanyRepository::SINGLETON_ID,
                    'key' => $key,
                ],
                [
                    'title' => $title,
                    'content' => null,
                ],
            );
        }
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, array{title: string, content: string|null}>
     */
    private function extractDocumentsPayload(array $data): array
    {
        $payload = [];

        foreach (array_keys(CompanyForm::documentDefinitions()) as $key) {
            $title = trim((string) ($data["document_{$key}_title"] ?? ''));
            $content = $data["document_{$key}_content"] ?? null;
            $content = is_string($content) && trim($content) !== '' ? $content : null;

            $payload[$key] = [
                'title' => $title !== '' ? $title : (CompanyForm::documentDefinitions()[$key] ?? $key),
                'content' => $content,
            ];
        }

        return $payload;
    }
}
