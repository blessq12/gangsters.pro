<?php

use App\Models\Company;
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
        Schema::create('company_legals', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Company::class);
            $table->string('legal_form');
            $table->string('legal_email');
            $table->string('owner');
            $table->string('inn');
            $table->string('ogrn');
            $table->string('okpo');
            $table->string('kpp');
            $table->string('registration_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_legals');
    }
};
