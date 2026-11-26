<?php

namespace App\Application\Company\Presenter;

use App\Domain\Supporting\Company\Entity\Company;
use App\Domain\Supporting\Company\Entity\CompanyDocument;
use App\Domain\Supporting\Company\Entity\CompanyLegalInfo;
use App\Shared\ValueObject\PhoneNumber;

/**
 * Единый формат ответа Company для витрины и админки.
 * Расписание — только в branch, у company его нет.
 */
final class CompanyPresenter
{
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

    private function formatOptionalPhone(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $phone = PhoneNumber::tryFromRaw($raw);

        return $phone?->formatted() ?? $raw;
    }
}
