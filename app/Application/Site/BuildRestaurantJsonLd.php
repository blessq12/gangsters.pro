<?php

namespace App\Application\Site;

use App\Application\SystemContent\Query\GetSystemCompanyUseCase;

final class BuildRestaurantJsonLd
{
    public function __construct(
        private readonly GetSystemCompanyUseCase $getSystemCompany,
    ) {}

    public function buildOrNull(): ?string
    {
        $payload = $this->getSystemCompany->execute();
        $company = $payload['data'] ?? null;
        if (! is_array($company)) {
            return null;
        }

        $canonicalBase = rtrim((string) config('site.canonical_base'), '/');
        $siteUrl = $canonicalBase !== '' ? $canonicalBase.'/' : '/';
        $ogSocialPath = (string) config('site.og_image_social_path');
        $imageUrl = $canonicalBase !== '' && $ogSocialPath !== ''
            ? $canonicalBase.$ogSocialPath
            : null;

        $name = trim((string) ($company['brand_name'] ?? $company['name'] ?? config('site.name')));
        if ($name === '') {
            $name = (string) config('site.name');
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $name,
            'url' => $siteUrl,
        ];

        $phone = trim((string) ($company['phone'] ?? $company['support_phone'] ?? ''));
        if ($phone !== '') {
            $schema['telephone'] = $phone;
        }

        $email = trim((string) ($company['public_email'] ?? $company['email_address'] ?? ''));
        if ($email !== '') {
            $schema['email'] = $email;
        }

        if ($imageUrl !== null) {
            $schema['image'] = $imageUrl;
        }

        $address = $this->postalAddress($company);
        if ($address !== null) {
            $schema['address'] = $address;
        }

        $workHours = trim((string) ($company['work_hours'] ?? ''));
        if ($workHours !== '') {
            $schema['openingHours'] = $workHours;
        }

        $encoded = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : null;
    }

    /**
     * @param  array<string, mixed>  $company
     * @return array<string, string>|null
     */
    private function postalAddress(array $company): ?array
    {
        $city = trim((string) ($company['city'] ?? ''));
        $street = trim((string) ($company['street'] ?? ''));
        $house = trim((string) ($company['house'] ?? ''));

        if ($city === '' && $street === '' && $house === '') {
            return null;
        }

        $streetLine = trim($street.($house !== '' ? ', д. '.$house : ''));

        $address = [
            '@type' => 'PostalAddress',
            'addressCountry' => trim((string) ($company['country'] ?? 'RU')) ?: 'RU',
        ];

        if ($city !== '') {
            $address['addressLocality'] = $city;
        }
        if ($streetLine !== '') {
            $address['streetAddress'] = $streetLine;
        }

        return $address;
    }
}
