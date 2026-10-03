<?php

namespace App\Mail\Support;

use App\Domain\Content\Repository\CompanyRepository;

/**
 * Имя бренда для писем: компания из БД, иначе mail.from.name / app.name.
 */
final class MailBrandName
{
    public static function resolve(?CompanyRepository $companies = null): string
    {
        $companies ??= app(CompanyRepository::class);
        $company = $companies->findPublic();
        $fromCompany = $company !== null ? trim($company->name()) : '';
        if ($fromCompany !== '') {
            return $fromCompany;
        }

        $fromMail = trim((string) config('mail.from.name', ''));
        if ($fromMail !== '') {
            return $fromMail;
        }

        return trim((string) config('app.name', ''));
    }
}
