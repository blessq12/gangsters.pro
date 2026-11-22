<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog — только справочник товаров (+ изображения).
 * Категории, теги, сеты и цены живут в меню филиала (CMP_branch_*).
 */
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
            $table->boolean('is_system')->default(false);
            $table->boolean('is_roll')->default(false);
            $table->boolean('is_complement')->default(false);
            $table->unsignedInteger('weight')->nullable();
            $table->json('nutrition')->nullable();
            $table->json('ingredients')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('CAT_item_images');
        Schema::dropIfExists('CAT_items');
    }
};
