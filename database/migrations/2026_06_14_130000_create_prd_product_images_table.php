<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('PRD_product_images')) {
            return;
        }

        Schema::create('PRD_product_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->cascadeOnDelete();
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRD_product_images');
    }
};
