<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active');

            $table->float('calories')->default(0);
            $table->float('proteins')->default(0);
            $table->float('fats')->default(0);
            $table->float('carbs')->default(0);
            $table->string('nutrition_basis')->default('per_100g');

            $table->timestamps();
            $table->timestamp('archived_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRD_products');
    }
};

