<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->onDelete('cascade');

            $table->integer('sort_order')->default(0);

            // варианта размеров для srcset
            $table->string('thumb_path')->nullable();
            $table->integer('thumb_width')->nullable();
            $table->integer('thumb_height')->nullable();

            $table->string('medium_path')->nullable();
            $table->integer('medium_width')->nullable();
            $table->integer('medium_height')->nullable();

            $table->string('large_path')->nullable();
            $table->integer('large_width')->nullable();
            $table->integer('large_height')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRD_product_images');
    }
};

