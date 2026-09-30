<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DAYS = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

    public function up(): void
    {
        Schema::table('CMP_company', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->json('socials')->nullable()->after('email');
            $table->json('schedule')->nullable()->after('socials');
        });

        $this->migrateCompanyRows();
        $this->migrateLegalOgrnFallback();

        Schema::table('CMP_company', function (Blueprint $table) {
            $table->dropColumn([
                'brand_name',
                'tagline',
                'phone_additional',
                'support_phone',
                'whatsapp_phone',
                'email_address',
                'public_email',
                'work_hours',
                'work_schedule',
                'logo',
                'telegram',
                'site_url',
                'vk',
                'inst',
            ]);
        });

        Schema::table('CMP_company_legal', function (Blueprint $table) {
            $table->dropColumn([
                'short_name',
                'legal_form',
                'legal_email',
                'contracts_email',
                'legal_phone',
                'owner',
                'responsible_person',
                'responsible_position',
                'ogrnip',
                'okpo',
                'kpp',
                'tax_system',
                'is_vat_payer',
                'vat_rate_default',
                'registration_address',
                'actual_address',
                'postal_address',
                'bank_name',
                'bik',
                'checking_account',
                'correspondent_account',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('CMP_company_legal', function (Blueprint $table) {
            $table->string('short_name')->nullable();
            $table->string('legal_form')->nullable();
            $table->string('legal_email')->nullable();
            $table->string('contracts_email')->nullable();
            $table->string('legal_phone')->nullable();
            $table->string('owner')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('ogrnip', 15)->nullable();
            $table->string('okpo', 10)->nullable();
            $table->string('kpp', 9)->nullable();
            $table->string('tax_system')->nullable();
            $table->boolean('is_vat_payer')->default(false);
            $table->unsignedTinyInteger('vat_rate_default')->default(0);
            $table->text('registration_address')->nullable();
            $table->text('actual_address')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bik', 9)->nullable();
            $table->string('checking_account', 20)->nullable();
            $table->string('correspondent_account', 20)->nullable();
        });

        Schema::table('CMP_company', function (Blueprint $table) {
            $table->string('brand_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('phone_additional')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('whatsapp_phone')->nullable();
            $table->string('email_address')->nullable();
            $table->string('public_email')->nullable();
            $table->string('work_hours')->nullable();
            $table->json('work_schedule')->nullable();
            $table->string('logo')->nullable();
            $table->string('telegram')->nullable();
            $table->string('site_url')->nullable();
            $table->string('vk')->nullable();
            $table->string('inst')->nullable();
        });

        $this->restoreCompanyRowsFromNewShape();

        Schema::table('CMP_company', function (Blueprint $table) {
            $table->dropColumn(['email', 'socials', 'schedule']);
        });
    }

    private function migrateCompanyRows(): void
    {
        $rows = DB::table('CMP_company')->get();

        foreach ($rows as $row) {
            $email = $this->nullableString($row->public_email ?? null)
                ?? $this->nullableString($row->email_address ?? null);

            $socials = [
                'telegram' => $this->nullableString($row->telegram ?? null),
                'vk' => $this->nullableString($row->vk ?? null),
                'inst' => $this->nullableString($row->inst ?? null),
                'site_url' => $this->nullableString($row->site_url ?? null),
                'whatsapp' => $this->nullableString($row->whatsapp_phone ?? null),
            ];

            $schedule = $this->scheduleArrayToObject($row->work_schedule ?? null);

            DB::table('CMP_company')->where('id', $row->id)->update([
                'email' => $email,
                'socials' => json_encode($socials, JSON_UNESCAPED_UNICODE),
                'schedule' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function migrateLegalOgrnFallback(): void
    {
        if (! Schema::hasColumn('CMP_company_legal', 'ogrnip')) {
            return;
        }

        $rows = DB::table('CMP_company_legal')->get(['id', 'ogrn', 'ogrnip']);

        foreach ($rows as $row) {
            $ogrn = $this->nullableString($row->ogrn ?? null);
            if ($ogrn !== null) {
                continue;
            }

            $ogrnip = $this->nullableString($row->ogrnip ?? null);
            if ($ogrnip === null) {
                continue;
            }

            DB::table('CMP_company_legal')->where('id', $row->id)->update([
                'ogrn' => $ogrnip,
            ]);
        }
    }

    /**
     * @return array<string, array{work: string|null, is_day_off: bool}>
     */
    private function scheduleArrayToObject(mixed $raw): array
    {
        $decoded = $raw;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
        }

        $out = [];
        foreach (self::DAYS as $day) {
            $out[$day] = [
                'work' => null,
                'is_day_off' => false,
            ];
        }

        if (! is_array($decoded)) {
            return $out;
        }

        // Already object-shaped?
        if ($this->looksLikeScheduleObject($decoded)) {
            foreach (self::DAYS as $day) {
                $row = $decoded[$day] ?? null;
                if (! is_array($row)) {
                    continue;
                }
                $out[$day] = [
                    'work' => $this->nullableString($row['work'] ?? null),
                    'is_day_off' => $this->toBool($row['is_day_off'] ?? false),
                ];
            }

            return $out;
        }

        foreach ($decoded as $item) {
            if (! is_array($item)) {
                continue;
            }
            $day = strtolower(trim((string) ($item['day'] ?? '')));
            if (! in_array($day, self::DAYS, true)) {
                continue;
            }
            $out[$day] = [
                'work' => $this->nullableString($item['work'] ?? null),
                'is_day_off' => $this->toBool($item['is_day_off'] ?? false),
            ];
        }

        return $out;
    }

    /**
     * @param  array<mixed>  $decoded
     */
    private function looksLikeScheduleObject(array $decoded): bool
    {
        foreach (self::DAYS as $day) {
            if (array_key_exists($day, $decoded)) {
                return true;
            }
        }

        return false;
    }

    private function restoreCompanyRowsFromNewShape(): void
    {
        $rows = DB::table('CMP_company')->get();

        foreach ($rows as $row) {
            $socials = json_decode((string) ($row->socials ?? '{}'), true);
            if (! is_array($socials)) {
                $socials = [];
            }

            $scheduleObj = json_decode((string) ($row->schedule ?? '{}'), true);
            $workSchedule = [];
            if (is_array($scheduleObj)) {
                foreach (self::DAYS as $day) {
                    $cell = $scheduleObj[$day] ?? null;
                    $workSchedule[] = [
                        'day' => $day,
                        'work' => is_array($cell) ? ($cell['work'] ?? null) : null,
                        'is_day_off' => is_array($cell) ? $this->toBool($cell['is_day_off'] ?? false) : false,
                    ];
                }
            }

            DB::table('CMP_company')->where('id', $row->id)->update([
                'email_address' => $row->email,
                'public_email' => $row->email,
                'telegram' => $socials['telegram'] ?? null,
                'vk' => $socials['vk'] ?? null,
                'inst' => $socials['inst'] ?? null,
                'site_url' => $socials['site_url'] ?? null,
                'whatsapp_phone' => $socials['whatsapp'] ?? null,
                'work_schedule' => json_encode($workSchedule, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function toBool(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
};
