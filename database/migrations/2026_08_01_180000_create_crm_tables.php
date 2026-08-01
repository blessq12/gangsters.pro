<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CRM_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->unique();
            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('password');
            $table->boolean('consent_personal_data')->default(false);
            $table->boolean('consent_marketing')->default(false);
            $table->json('addresses')->nullable();
            $table->json('favorite_product_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('CRM_order_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('CRM_clients')->cascadeOnDelete();
            $table->json('order_snapshot');
            $table->timestamp('placed_at');
            $table->timestamps();

            $table->index(['client_id', 'placed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CRM_order_history');
        Schema::dropIfExists('CRM_clients');
    }
};
