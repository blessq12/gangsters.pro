<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Catalog (CAT_): справочник товаров + техкарты. */
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

        Schema::create('CAT_tech_cards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('CAT_items')->cascadeOnDelete();
        });

        Schema::create('CAT_tech_card_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tech_card_id');
            $table->string('ingredient_ref', 191);
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 64);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tech_card_id')->references('id')->on('CAT_tech_cards')->cascadeOnDelete();
            $table->index(['tech_card_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CAT_tech_card_lines');
        Schema::dropIfExists('CAT_tech_cards');
        Schema::dropIfExists('CAT_item_images');
        Schema::dropIfExists('CAT_items');
    }
};
