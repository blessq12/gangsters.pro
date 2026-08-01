<?php

use App\Shared\ValueObject\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CLN_clients', function (Blueprint $table): void {
            $table->string('phone', 20)->change();
        });

        Schema::table('CMP_company', function (Blueprint $table): void {
            $table->string('phone', 20)->nullable()->change();
            $table->string('phone_additional', 20)->nullable()->change();
            $table->string('support_phone', 20)->nullable()->change();
            $table->string('whatsapp_phone', 20)->nullable()->change();
        });

        Schema::table('CMP_company_legal', function (Blueprint $table): void {
            $table->string('legal_phone', 20)->nullable()->change();
        });

        $this->migrateClientPhones();
        $this->migrateCompanyPhones();
        $this->migrateOrderClientSnapshotPhones();
    }

    public function down(): void
    {
        Schema::table('CLN_clients', function (Blueprint $table): void {
            $table->string('phone', 16)->change();
        });

        Schema::table('CMP_company', function (Blueprint $table): void {
            $table->string('phone')->nullable()->change();
            $table->string('phone_additional')->nullable()->change();
            $table->string('support_phone')->nullable()->change();
            $table->string('whatsapp_phone')->nullable()->change();
        });

        Schema::table('CMP_company_legal', function (Blueprint $table): void {
            $table->string('legal_phone')->nullable()->change();
        });
    }

    private function migrateClientPhones(): void
    {
        DB::table('CLN_clients')
            ->select(['id', 'phone'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $formatted = PhoneNumber::tryFormatFromRaw((string) $row->phone);
                    if ($formatted === null || $formatted === $row->phone) {
                        continue;
                    }

                    DB::table('CLN_clients')
                        ->where('id', $row->id)
                        ->update(['phone' => $formatted]);
                }
            });
    }

    private function migrateCompanyPhones(): void
    {
        $companyColumns = ['phone', 'phone_additional', 'support_phone', 'whatsapp_phone'];

        DB::table('CMP_company')->orderBy('id')->each(function ($row) use ($companyColumns): void {
            $updates = [];

            foreach ($companyColumns as $column) {
                $raw = $row->{$column} ?? null;
                if (! is_string($raw) || trim($raw) === '') {
                    continue;
                }

                $formatted = PhoneNumber::tryFormatFromRaw($raw);
                if ($formatted !== null && $formatted !== $raw) {
                    $updates[$column] = $formatted;
                }
            }

            if ($updates !== []) {
                DB::table('CMP_company')->where('id', $row->id)->update($updates);
            }
        });

        DB::table('CMP_company_legal')->orderBy('id')->each(function ($row): void {
            $raw = $row->legal_phone ?? null;
            if (! is_string($raw) || trim($raw) === '') {
                return;
            }

            $formatted = PhoneNumber::tryFormatFromRaw($raw);
            if ($formatted !== null && $formatted !== $raw) {
                DB::table('CMP_company_legal')
                    ->where('id', $row->id)
                    ->update(['legal_phone' => $formatted]);
            }
        });
    }

    private function migrateOrderClientSnapshotPhones(): void
    {
        if (! Schema::hasTable('ORD_orders')) {
            return;
        }

        DB::table('ORD_orders')
            ->select(['id', 'client_snapshot'])
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $snapshot = json_decode((string) $row->client_snapshot, true);
                    if (! is_array($snapshot) || ! isset($snapshot['phone'])) {
                        continue;
                    }

                    $formatted = PhoneNumber::tryFormatFromRaw((string) $snapshot['phone']);
                    if ($formatted === null || $formatted === $snapshot['phone']) {
                        continue;
                    }

                    $snapshot['phone'] = $formatted;

                    DB::table('ORD_orders')
                        ->where('id', $row->id)
                        ->update(['client_snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE)]);
                }
            });
    }
};
