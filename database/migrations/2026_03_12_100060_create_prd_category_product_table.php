<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('PRD_categories')
                ->onDelete('cascade');
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->onDelete('cascade');

            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRD_category_product');
    }
};

