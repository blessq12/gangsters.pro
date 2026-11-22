<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Расписание только у филиала.
 * Идемпотентно: на fresh (schedule уже в CMP_branches) — no-op по колонкам;
 * на старых БД — перенос с CMP_companies и drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('CMP_branches', 'schedule')) {
            Schema::table('CMP_branches', function (Blueprint $table): void {
                $table->json('schedule')->nullable()->after('is_active');
            });
        }

        if (! Schema::hasColumn('CMP_companies', 'schedule')) {
            return;
        }

        $companies = DB::table('CMP_companies')->select(['id', 'schedule'])->get();
        foreach ($companies as $company) {
            $schedule = $company->schedule;
            if (is_string($schedule)) {
                $decoded = json_decode($schedule, true);
                $schedule = is_array($decoded) ? $decoded : [];
            }
            if (! is_array($schedule)) {
                $schedule = [];
            }

            DB::table('CMP_branches')
                ->where('company_id', $company->id)
                ->update([
                    'schedule' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }

        Schema::table('CMP_companies', function (Blueprint $table): void {
            $table->dropColumn('schedule');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('CMP_companies', 'schedule')) {
            Schema::table('CMP_companies', function (Blueprint $table): void {
                $table->json('schedule')->nullable()->after('socials');
            });
        }

        if (! Schema::hasColumn('CMP_branches', 'schedule')) {
            return;
        }

        $branches = DB::table('CMP_branches')
            ->select(['company_id', 'schedule'])
            ->orderBy('id')
            ->get()
            ->groupBy('company_id');

        foreach ($branches as $companyId => $rows) {
            $schedule = $rows[0]->schedule ?? null;
            if (is_string($schedule)) {
                $decoded = json_decode($schedule, true);
                $schedule = is_array($decoded) ? $decoded : [];
            }
            if (! is_array($schedule)) {
                $schedule = [];
            }

            DB::table('CMP_companies')
                ->where('id', $companyId)
                ->update([
                    'schedule' => json_encode($schedule, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                ]);
        }

        Schema::table('CMP_branches', function (Blueprint $table): void {
            $table->dropColumn('schedule');
        });
    }
};
