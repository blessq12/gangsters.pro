<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('CLN_client_favorites');
    }
};
