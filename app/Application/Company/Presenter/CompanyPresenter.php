<?php

namespace App\Application\Company\Presenter;

use App\Domain\Supporting\Company\Entity\Branch;
use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\Entity\CompanyDocument;
use App\Domain\Supporting\Company\Entity\CompanyLegalInfo;
use App\Domain\Supporting\Company\Entity\Kitchen;
use App\Shared\ValueObject\PhoneNumber;

/**
 * Единый формат ответа Company для витрины и админки.
 */
final class CompanyPresenter
{
    /** @var list<string> */
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    /**
     * @return array<string, mixed>
     */
    public function company(Company $company): array
    {
        $socials = $company->socials()->toArray();
        $socials['whatsapp'] = $this->formatOptionalPhone($socials['whatsapp'] ?? null);

        return [
            'id' => $company->id(),
            'name' => $company->name(),
            'description' => $company->description(),
            'phone' => $this->formatOptionalPhone($company->phone()),
            'email' => $company->email(),
            'socials' => $socials,
            'schedule' => $this->schedule($company->schedule()),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function legal(?CompanyLegalInfo $legal): ?array
    {
        if (! $legal instanceof CompanyLegalInfo) {
            return null;
        }

        return [
            'id' => $legal->id(),
            'company_id' => $legal->companyId(),
            'full_name' => $legal->fullName(),
            'inn' => $legal->inn(),
            'ogrn' => $legal->ogrn(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function document(CompanyDocument $document): array
    {
        return [
            'id' => $document->id(),
            'slug' => $document->slug(),
            'name' => $document->name(),
            'content' => $document->content(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function branch(Branch $branch): array
    {
        return [
            'id' => $branch->id(),
            'company_id' => $branch->companyId(),
            'name' => $branch->name(),
            'is_active' => $branch->isActive(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function kitchen(Kitchen $kitchen): array
    {
        return [
            'id' => $kitchen->id(),
            'branch_id' => $kitchen->branchId(),
            'city' => $kitchen->city(),
            'street' => $kitchen->street(),
            'house' => $kitchen->house(),
            'comment' => $kitchen->comment(),
            'latitude' => $kitchen->latitude(),
            'longitude' => $kitchen->longitude(),
        ];
    }

    /**
     * @param  array<string, array{work: ?string, is_day_off: bool}>  $schedule
     * @return array<string, array{work: ?string, is_day_off: bool}>
     */
    private function schedule(array $schedule): array
    {
        $normalized = [];
        foreach (self::DAYS as $day) {
            $row = $schedule[$day] ?? null;
            $normalized[$day] = [
                'work' => is_array($row) ? ($row['work'] ?? null) : null,
                'is_day_off' => is_array($row) ? (bool) ($row['is_day_off'] ?? false) : false,
            ];
        }

        return $normalized;
    }

    private function formatOptionalPhone(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $phone = PhoneNumber::tryFromRaw($raw);

        return $phone?->formatted() ?? $raw;
    }
}
