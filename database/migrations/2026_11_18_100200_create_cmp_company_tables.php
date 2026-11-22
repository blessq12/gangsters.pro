<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CMP_companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('socials')->nullable();
            $table->timestamps();
        });

        Schema::create('CMP_company_legal', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('full_name')->nullable();
            $table->string('inn')->nullable();
            $table->string('ogrn')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('CMP_companies')->cascadeOnDelete();
        });

        Schema::create('CMP_company_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('slug');
            $table->string('name');
            $table->longText('content')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('CMP_companies')->cascadeOnDelete();
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('CMP_branches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->json('schedule')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('house')->nullable();
            $table->string('comment')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('CMP_companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CMP_branches');
        Schema::dropIfExists('CMP_company_documents');
        Schema::dropIfExists('CMP_company_legal');
        Schema::dropIfExists('CMP_companies');
    }
};
