<?php

namespace App\Filament\Resources\Companies\Concerns;

use App\Support\SystemContent\CompanyPhoneField;
use App\Support\SystemContent\DocumentKeyLabels;

trait NormalizesCompanySettingsData
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function stripNestedCompanySettings(array $data): array
    {
        unset($data['legal'], $data['documents']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeCompanyPhones(array $data): array
    {
        foreach (['phone', 'phone_additional', 'support_phone', 'whatsapp_phone'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = CompanyPhoneField::normalize(
                    is_string($data[$field]) ? $data[$field] : null
                );
            }
        }

        if (isset($data['legal']) && is_array($data['legal']) && array_key_exists('legal_phone', $data['legal'])) {
            $data['legal']['legal_phone'] = CompanyPhoneField::normalize(
                is_string($data['legal']['legal_phone']) ? $data['legal']['legal_phone'] : null
            );
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function syncLegacyWorkHoursFromSchedule(array $data): array
    {
        $schedule = $data['work_schedule'] ?? null;
        if (! is_array($schedule)) {
            return $data;
        }

        $works = [];
        foreach ($schedule as $row) {
            if (! is_array($row)) {
                continue;
            }

            $isDayOff = $row['is_day_off'] ?? '0';
            if ($isDayOff === '1' || $isDayOff === 1 || $isDayOff === true) {
                continue;
            }

            $work = trim((string) ($row['work'] ?? ''));
            if ($work !== '') {
                $works[] = $work;
            }
        }

        if ($works === []) {
            return $data;
        }

        $unique = array_values(array_unique($works));
        $data['work_hours'] = count($unique) === 1 ? $unique[0] : $works[0];

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultLegalAttributes(): array
    {
        return [
            'legal_form' => 'Не указано',
            'legal_email' => 'noreply@example.com',
            'owner' => 'Не указано',
            'inn' => '0000000000',
            'ogrn' => '0000000000000',
            'okpo' => '00000000',
            'kpp' => '000000000',
            'registration_address' => 'Не указано',
        ];
    }

    /**
     * @return array<string, array{title: string, content: string, is_active: bool}>
     */
    protected function buildDocumentsFormState(): array
    {
        $documents = [];
        $existing = \App\Infrastructure\SystemContent\Model\SYS_Document::query()
            ->whereIn('key', DocumentKeyLabels::keys())
            ->get()
            ->keyBy('key');

        foreach (DocumentKeyLabels::keys() as $key) {
            $row = $existing->get($key);
            $documents[$key] = [
                'title' => $row?->title ?? DocumentKeyLabels::defaultTitle($key),
                'content' => $row?->content ?? '',
                'is_active' => (bool) ($row?->is_active ?? true),
            ];
        }

        return $documents;
    }

    /**
     * @param  array<string, mixed>  $legalData
     */
    protected function persistCompanyLegal(int $companyId, array $legalData): void
    {
        if ($legalData === []) {
            return;
        }

        unset($legalData['id'], $legalData['company_id'], $legalData['created_at'], $legalData['updated_at']);

        \App\Infrastructure\SystemContent\Model\SYS_CompanyLegal::query()->updateOrCreate(
            ['company_id' => $companyId],
            $legalData
        );
    }

    /**
     * @param  array<string, array<string, mixed>>  $documentsData
     */
    protected function persistCompanyDocuments(array $documentsData): void
    {
        foreach (DocumentKeyLabels::keys() as $key) {
            $payload = $documentsData[$key] ?? null;
            if (! is_array($payload)) {
                continue;
            }

            \App\Infrastructure\SystemContent\Model\SYS_Document::query()->updateOrCreate(
                ['key' => $key],
                [
                    'title' => (string) ($payload['title'] ?? DocumentKeyLabels::defaultTitle($key)),
                    'content' => (string) ($payload['content'] ?? ''),
                    'is_active' => (bool) ($payload['is_active'] ?? true),
                ]
            );
        }
    }
}
