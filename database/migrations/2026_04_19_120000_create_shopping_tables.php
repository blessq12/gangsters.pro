<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SHP_shopping_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('client_id')->nullable()->constrained('UR_clients')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('SHP_shopping_cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_session_id')->constrained('SHP_shopping_sessions')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->unsignedInteger('quantity');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['shopping_session_id', 'product_id']);
        });

        Schema::create('SHP_shopping_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_session_id')->constrained('SHP_shopping_sessions')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->unique(['shopping_session_id', 'product_id']);
        });

        Schema::create('SHP_shopping_checkout_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_session_id')->constrained('SHP_shopping_sessions')->cascadeOnDelete();
            $table->json('payload');
            $table->timestamps();

            $table->unique('shopping_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SHP_shopping_checkout_drafts');
        Schema::dropIfExists('SHP_shopping_favorites');
        Schema::dropIfExists('SHP_shopping_cart_lines');
        Schema::dropIfExists('SHP_shopping_sessions');
    }
};
