<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\LoadsSeederJson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Компания + единственный боевой филиал (Говорова 50/1). Смоленский не сидируется.
 */
class CompanySeeder extends Seeder
{
    use LoadsSeederJson;

    public function run(): void
    {
        $companyPayload = $this->loadSeederJson('company.json');
        $branch = $this->loadSeederJson('branch.json');

        $company = is_array($companyPayload['company'] ?? null) ? $companyPayload['company'] : null;
        $legal = is_array($companyPayload['legal'] ?? null) ? $companyPayload['legal'] : null;
        $documents = is_array($companyPayload['documents'] ?? null) ? $companyPayload['documents'] : [];

        if ($company === null || $branch === []) {
            throw new RuntimeException('company.json / branch.json повреждены.');
        }

        DB::transaction(function () use ($company, $legal, $documents, $branch): void {
            DB::table('CMP_company_documents')->delete();
            DB::table('CMP_company_legal')->delete();
            DB::table('CMP_branches')->delete();
            DB::table('CMP_companies')->delete();

            DB::table('CMP_companies')->insert([
                'id' => (int) $company['id'],
                'name' => (string) $company['name'],
                'description' => $company['description'] ?? null,
                'phone' => $company['phone'] ?? null,
                'email' => $company['email'] ?? null,
                'socials' => $this->encodeJson($company['socials'] ?? null),
                'created_at' => $company['created_at'] ?? now(),
                'updated_at' => $company['updated_at'] ?? now(),
            ]);

            if ($legal !== null) {
                DB::table('CMP_company_legal')->insert([
                    'id' => (int) $legal['id'],
                    'company_id' => (int) $legal['company_id'],
                    'full_name' => $legal['full_name'] ?? null,
                    'inn' => $legal['inn'] ?? null,
                    'ogrn' => $legal['ogrn'] ?? null,
                    'created_at' => $legal['created_at'] ?? now(),
                    'updated_at' => $legal['updated_at'] ?? now(),
                ]);
            }

            foreach ($documents as $document) {
                if (! is_array($document)) {
                    continue;
                }
                DB::table('CMP_company_documents')->insert([
                    'id' => (int) $document['id'],
                    'company_id' => (int) $document['company_id'],
                    'slug' => (string) $document['slug'],
                    'name' => (string) $document['name'],
                    'content' => $document['content'] ?? null,
                    'path' => $document['path'] ?? null,
                    'created_at' => $document['created_at'] ?? now(),
                    'updated_at' => $document['updated_at'] ?? now(),
                ]);
            }

            DB::table('CMP_branches')->insert([
                'id' => (int) $branch['id'],
                'company_id' => (int) $branch['company_id'],
                'name' => (string) $branch['name'],
                'is_active' => (bool) ($branch['is_active'] ?? true),
                'schedule' => $this->encodeJson($branch['schedule'] ?? null),
                'city' => $branch['city'] ?? null,
                'street' => $branch['street'] ?? null,
                'house' => $branch['house'] ?? null,
                'comment' => $branch['comment'] ?? null,
                'latitude' => $branch['latitude'] ?? null,
                'longitude' => $branch['longitude'] ?? null,
                'phone' => $branch['phone'] ?? null,
                'created_at' => $branch['created_at'] ?? now(),
                'updated_at' => $branch['updated_at'] ?? now(),
            ]);
        });
    }

    private function encodeJson(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
