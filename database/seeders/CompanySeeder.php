<?php

namespace Database\Seeders;

use App\Domain\Supporting\Company\Repository\CompanyRepository;
use App\Infrastructure\Company\Model\CMP_Branch;
use App\Infrastructure\Company\Model\CMP_Company;
use App\Infrastructure\Company\Model\CMP_CompanyDocument;
use App\Infrastructure\Company\Model\CMP_CompanyLegal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Компания + филиал (адрес и координаты в самом филиале) из /api/content → CMP_*.
 */
class CompanySeeder extends Seeder
{
    private const CONTENT_SOURCE_URL = 'https://gangsta-sushi.ru/api/content';

    public function run(): void
    {
        $payload = $this->fetchContentPayload();
        $companyBlock = is_array($payload['company'] ?? null) ? $payload['company'] : [];
        $deliveryBlock = is_array($payload['delivery'] ?? null) ? $payload['delivery'] : [];

        $main = is_array($companyBlock['main'] ?? null) ? $companyBlock['main'] : [];
        $legal = is_array($companyBlock['legals'] ?? null) ? $companyBlock['legals'] : [];
        $documents = is_array($companyBlock['documents'] ?? null) ? $companyBlock['documents'] : [];

        $companyId = CompanyRepository::SINGLETON_ID;

        CMP_Company::query()->updateOrCreate(
            ['id' => $companyId],
            [
                'name' => (string) ($main['name'] ?? 'Гангстерс Суши'),
                'description' => $main['description'] ?? null,
                'phone' => $main['phone'] ?? null,
                'email' => $main['email'] ?? null,
                'socials' => is_array($main['socials'] ?? null) ? $main['socials'] : [],
            ],
        );

        CMP_CompanyLegal::query()->updateOrCreate(
            ['company_id' => $companyId],
            [
                'full_name' => $legal['full_name'] ?? null,
                'inn' => $legal['inn'] ?? null,
                'ogrn' => $legal['ogrn'] ?? null,
            ],
        );

        $keptSlugs = [];
        foreach ($documents as $document) {
            if (! is_array($document)) {
                continue;
            }

            $slug = trim((string) ($document['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $keptSlugs[] = $slug;

            CMP_CompanyDocument::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'slug' => $slug,
                ],
                [
                    'name' => (string) ($document['name'] ?? $slug),
                    'content' => $document['content'] ?? null,
                    'path' => $document['path'] ?? null,
                ],
            );
        }

        if ($keptSlugs !== []) {
            CMP_CompanyDocument::query()
                ->where('company_id', $companyId)
                ->whereNotIn('slug', $keptSlugs)
                ->delete();
        }

        $zone = is_array($deliveryBlock['zone'] ?? null) ? $deliveryBlock['zone'] : [];
        $address = is_array($zone['kitchen_address'] ?? null) ? $zone['kitchen_address'] : [];

        CMP_Branch::query()->updateOrCreate(
            [
                'company_id' => $companyId,
                'name' => 'Томск',
            ],
            [
                'is_active' => true,
                'schedule' => is_array($main['schedule'] ?? null) ? $main['schedule'] : [],
                'city' => $address['city'] ?? 'Томск',
                'street' => $address['street'] ?? 'ул. Говорова',
                'house' => $address['house'] ?? '50/1',
                'comment' => $address['comment'] ?? null,
                'latitude' => isset($zone['kitchen_latitude']) ? (float) $zone['kitchen_latitude'] : 56.513432,
                'longitude' => isset($zone['kitchen_longitude']) ? (float) $zone['kitchen_longitude'] : 84.986366,
                'phone' => $main['phone'] ?? null,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchContentPayload(): array
    {
        $response = Http::timeout(60)
            ->acceptJson()
            ->retry(2, 500)
            ->get(self::CONTENT_SOURCE_URL);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Не удалось загрузить content: HTTP '.$response->status(),
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Некорректный формат content API.');
        }

        return $payload;
    }
}
