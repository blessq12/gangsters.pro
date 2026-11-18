<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('WH_ingredients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('unit', 64);
            $table->timestamps();
        });

        Schema::create('WH_stock_levels', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ingredient_id')->unique();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->timestamps();

            $table->foreign('ingredient_id')->references('id')->on('WH_ingredients')->cascadeOnDelete();
        });

        Schema::create('WH_stock_writeoffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ingredient_id');
            $table->decimal('quantity', 12, 3);
            $table->string('reason', 500);
            $table->unsignedBigInteger('kitchen_ticket_id')->nullable()->index();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('ingredient_id')->references('id')->on('WH_ingredients')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('WH_stock_writeoffs');
        Schema::dropIfExists('WH_stock_levels');
        Schema::dropIfExists('WH_ingredients');
    }
};
