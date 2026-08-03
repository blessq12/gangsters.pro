<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Удаляет legacy-таблицы, которые приложение больше не использует.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('CLN_client_favorites');
        Schema::dropIfExists('CLN_client_addresses');
        Schema::dropIfExists('CLN_password_reset_tokens');
        Schema::dropIfExists('CLN_clients');

        Schema::dropIfExists('CHK_checkouts');
        Schema::dropIfExists('ING_partner_sku_bindings');
        Schema::dropIfExists('ING_ingress_audits');
        Schema::dropIfExists('OAE_export_attempts');
    }

    public function down(): void
    {
        Schema::create('CLN_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 16)->unique();
            $table->string('email')->unique();
            $table->date('birth_date')->nullable();
            $table->string('password');
            $table->boolean('consent_personal_data')->default(false);
            $table->boolean('consent_marketing')->default(false);
            $table->timestamps();
        });

        Schema::create('CLN_client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('CLN_clients')->cascadeOnDelete();
            $table->string('type', 32)->nullable();
            $table->string('title')->nullable();
            $table->string('street');
            $table->string('house', 32);
            $table->string('entrance', 32)->nullable();
            $table->string('apartment', 32)->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('CLN_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('CLN_client_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('CLN_clients')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->string('product_name')->nullable();
            $table->decimal('price_rub', 10, 2)->nullable();
            $table->string('weight', 64)->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'product_id']);
        });

        Schema::create('CHK_checkouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 32);
            $table->json('cart_snapshot');
            $table->json('client_snapshot')->nullable();
            $table->json('delivery_snapshot')->nullable();
            $table->json('payment_snapshot')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // ING_*/OAE_* создавались вне репозитория — down только пустые каркасы под drop.
        Schema::create('ING_ingress_audits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('ING_partner_sku_bindings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('OAE_export_attempts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
