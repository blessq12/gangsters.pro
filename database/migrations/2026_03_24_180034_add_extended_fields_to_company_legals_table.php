<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_legals', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('company_id');
            $table->string('short_name')->nullable()->after('full_name');
            $table->string('ogrnip')->nullable()->after('ogrn');
            $table->string('tax_system')->nullable()->after('kpp');
            $table->string('actual_address')->nullable()->after('registration_address');
            $table->string('postal_address')->nullable()->after('actual_address');
            $table->string('bank_name')->nullable()->after('postal_address');
            $table->string('bik')->nullable()->after('bank_name');
            $table->string('checking_account')->nullable()->after('bik');
            $table->string('correspondent_account')->nullable()->after('checking_account');
            $table->string('contracts_email')->nullable()->after('legal_email');
            $table->string('legal_phone')->nullable()->after('contracts_email');
            $table->string('responsible_person')->nullable()->after('owner');
            $table->string('responsible_position')->nullable()->after('responsible_person');
            $table->boolean('is_vat_payer')->default(false)->after('tax_system');
            $table->unsignedTinyInteger('vat_rate_default')->nullable()->after('is_vat_payer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_legals', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'short_name',
                'ogrnip',
                'tax_system',
                'actual_address',
                'postal_address',
                'bank_name',
                'bik',
                'checking_account',
                'correspondent_account',
                'contracts_email',
                'legal_phone',
                'responsible_person',
                'responsible_position',
                'is_vat_payer',
                'vat_rate_default',
            ]);
        });
    }
};
