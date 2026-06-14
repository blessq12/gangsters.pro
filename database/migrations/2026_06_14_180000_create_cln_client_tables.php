<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('CLN_password_reset_tokens');
        Schema::dropIfExists('CLN_client_addresses');
        Schema::dropIfExists('CLN_clients');
    }
};
