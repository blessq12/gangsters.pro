<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('PRD_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->string('catalog_kind', 16)->default('product');
            $table->unsignedBigInteger('price')->nullable()->comment('Цена в рублях');
            $table->float('calories')->default(0);
            $table->float('proteins')->default(0);
            $table->float('fats')->default(0);
            $table->float('carbs')->default(0);
            $table->string('nutrition_basis')->default('per_100g');
            $table->json('ingredients')->nullable();
            $table->boolean('meta_counts_as_roll')->default(false);
            $table->boolean('meta_gift_candidate')->default(false);
            $table->boolean('meta_is_complement_set')->default(false);
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();
        });

        Schema::create('PRD_category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('PRD_categories')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('PRD_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->string('color', 20)->default('amber');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('PRD_product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained('PRD_tags')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'tag_id']);
        });

        Schema::create('PRD_product_set_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_id')
                ->constrained('PRD_products')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['set_id', 'product_id']);
        });

        Schema::create('PRD_product_images', function (Blueprint $table) {
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
        Schema::dropIfExists('PRD_product_set_lines');
        Schema::dropIfExists('PRD_product_tag');
        Schema::dropIfExists('PRD_tags');
        Schema::dropIfExists('PRD_category_product');
        Schema::dropIfExists('PRD_products');
        Schema::dropIfExists('PRD_categories');
    }
};
