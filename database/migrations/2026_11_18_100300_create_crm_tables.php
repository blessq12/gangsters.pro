<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CRM_clients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('password_hash');
            $table->boolean('consent_personal_data')->default(false);
            $table->boolean('consent_marketing')->default(false);
            $table->timestamps();
        });

        Schema::create('CRM_client_addresses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('address_key');
            $table->string('type')->nullable();
            $table->string('title')->nullable();
            $table->string('street');
            $table->string('house');
            $table->string('apartment')->nullable();
            $table->string('entrance')->nullable();
            $table->string('floor')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('CRM_clients')->cascadeOnDelete();
            $table->unique(['client_id', 'address_key']);
        });

        Schema::create('CRM_client_favorites', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('catalog_item_id');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('CRM_clients')->cascadeOnDelete();
            $table->unique(['client_id', 'catalog_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CRM_client_favorites');
        Schema::dropIfExists('CRM_client_addresses');
        Schema::dropIfExists('CRM_clients');
    }
};
