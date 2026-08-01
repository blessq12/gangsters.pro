<?php

namespace App\Infrastructure\Content\Mapper;

use App\Domain\Content\Entity\Company;
use App\Domain\Content\ValueObject\CompanyContact;
use App\Domain\Content\ValueObject\CompanySchedule;
use App\Domain\Content\ValueObject\WorkScheduleRow;
use App\Infrastructure\Content\Model\CMP_Company;

final class CompanyMapper
{
    private const VALID_DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function toDomain(CMP_Company $row): Company
    {
        return new Company(
            id: (int) $row->id,
            name: (string) $row->name,
            brandName: $this->nullableString($row->brand_name),
            description: $this->nullableString($row->description),
            tagline: $this->nullableString($row->tagline),
            contact: new CompanyContact(
                phone: $this->nullableString($row->phone),
                phoneAdditional: $this->nullableString($row->phone_additional),
                supportPhone: $this->nullableString($row->support_phone),
                whatsappPhone: $this->nullableString($row->whatsapp_phone),
                emailAddress: $this->nullableString($row->email_address),
                publicEmail: $this->nullableString($row->public_email),
            ),
            schedule: new CompanySchedule(
                workHours: $this->nullableString($row->work_hours),
                workSchedule: $this->mapWorkSchedule($row->work_schedule),
            ),
            logo: $this->nullableString($row->logo),
            telegram: $this->nullableString($row->telegram),
            siteUrl: $this->nullableString($row->site_url),
            vk: $this->nullableString($row->vk),
            inst: $this->nullableString($row->inst),
        );
    }

    /**
     * @return list<WorkScheduleRow>
     */
    private function mapWorkSchedule(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $item) {
            if (! is_array($item)) {
                continue;
            }

            $day = isset($item['day']) ? strtolower(trim((string) $item['day'])) : '';
            if (! in_array($day, self::VALID_DAYS, true)) {
                continue;
            }

            $isDayOff = $item['is_day_off'] === true
                || $item['is_day_off'] === 1
                || $item['is_day_off'] === '1';

            $work = $this->nullableString($item['work'] ?? null);

            $rows[] = new WorkScheduleRow(
                day: $day,
                work: $work,
                isDayOff: $isDayOff,
            );
        }

        return $rows;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
