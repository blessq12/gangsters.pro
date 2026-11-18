<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CAT_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('status', 32);
            $table->string('kind', 16);
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('weight')->nullable();
            $table->json('nutrition')->nullable();
            $table->json('ingredients')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('CAT_prices', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('type', 16);
            $table->unsignedBigInteger('amount_kopecks');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
            $table->unique(['item_id', 'type']);
        });

        Schema::create('CAT_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_accompanying')->default(false);
            $table->timestamps();
        });

        Schema::create('CAT_category_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreign('category_id')->references('id')->on('CAT_categories')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
            $table->unique(['category_id', 'item_id']);
        });

        Schema::create('CAT_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->string('color', 32);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('CAT_item_tags', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('CAT_tags')->cascadeOnDelete();
            $table->unique(['item_id', 'tag_id']);
        });

        Schema::create('CAT_item_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('focus')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
        });

        Schema::create('CAT_set_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('set_item_id');
            $table->unsignedBigInteger('component_item_id');
            $table->unsignedInteger('quantity');

            $table->foreign('set_item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
            $table->foreign('component_item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
            $table->unique(['set_item_id', 'component_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CAT_set_lines');
        Schema::dropIfExists('CAT_item_images');
        Schema::dropIfExists('CAT_item_tags');
        Schema::dropIfExists('CAT_tags');
        Schema::dropIfExists('CAT_category_items');
        Schema::dropIfExists('CAT_categories');
        Schema::dropIfExists('CAT_prices');
        Schema::dropIfExists('CAT_items');
    }
};
