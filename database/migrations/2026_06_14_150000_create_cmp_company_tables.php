<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CMP_company', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand_name')->nullable();
            $table->text('description')->nullable();
            $table->string('tagline')->nullable();
            $table->string('phone')->nullable();
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
            $table->timestamps();
        });

        Schema::create('CMP_company_legal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('CMP_company')
                ->cascadeOnDelete();
            $table->string('full_name')->nullable();
            $table->string('short_name')->nullable();
            $table->string('legal_form')->nullable();
            $table->string('legal_email')->nullable();
            $table->string('contracts_email')->nullable();
            $table->string('legal_phone')->nullable();
            $table->string('owner')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('responsible_position')->nullable();
            $table->string('inn', 12)->nullable();
            $table->string('ogrn', 15)->nullable();
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
            $table->timestamps();
        });

        Schema::create('CMP_company_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('CMP_company')
                ->cascadeOnDelete();
            $table->string('key')->unique();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CMP_company_documents');
        Schema::dropIfExists('CMP_company_legal');
        Schema::dropIfExists('CMP_company');
    }
};
