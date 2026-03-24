<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $rows = DB::table('company_legals')
            ->leftJoin('companies', 'companies.id', '=', 'company_legals.company_id')
            ->select([
                'company_legals.id',
                'company_legals.legal_form',
                'company_legals.legal_email',
                'company_legals.owner',
                'company_legals.inn',
                'company_legals.ogrn',
                'company_legals.okpo',
                'company_legals.kpp',
                'company_legals.registration_address',
                'companies.name as company_name',
                'companies.phone as company_phone',
            ])
            ->get();

        foreach ($rows as $row) {
            $owner = trim((string) ($row->owner ?? ''));
            $legalForm = trim((string) ($row->legal_form ?? ''));
            $companyName = trim((string) ($row->company_name ?? ''));

            $fullName = $companyName !== '' ? $companyName : ($owner !== '' ? $owner : null);
            $shortName = $owner !== '' ? $owner : $companyName;
            $responsiblePerson = $owner !== '' ? $owner : null;
            $contractsEmail = $row->legal_email ?: null;
            $legalPhone = $row->company_phone ?: null;
            $taxSystem = null;
            if (str_contains(mb_strtolower($legalForm), 'ип')) {
                $taxSystem = 'УСН';
            }

            DB::table('company_legals')
                ->where('id', $row->id)
                ->update([
                    'full_name' => $fullName,
                    'short_name' => $shortName !== '' ? $shortName : null,
                    'tax_system' => $taxSystem,
                    'actual_address' => $row->registration_address,
                    'postal_address' => $row->registration_address,
                    'contracts_email' => $contractsEmail,
                    'legal_phone' => $legalPhone,
                    'responsible_person' => $responsiblePerson,
                    'responsible_position' => 'Ответственный',
                    'is_vat_payer' => false,
                    'vat_rate_default' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('company_legals')->update([
            'full_name' => null,
            'short_name' => null,
            'ogrnip' => null,
            'tax_system' => null,
            'actual_address' => null,
            'postal_address' => null,
            'bank_name' => null,
            'bik' => null,
            'checking_account' => null,
            'correspondent_account' => null,
            'contracts_email' => null,
            'legal_phone' => null,
            'responsible_person' => null,
            'responsible_position' => null,
            'is_vat_payer' => false,
            'vat_rate_default' => null,
            'updated_at' => now(),
        ]);
    }
};
