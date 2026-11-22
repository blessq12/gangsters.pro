<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog: технологическая карта товара 1:1 (строки с opaque ingredient_ref).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CAT_tech_cards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id')->unique();
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('CAT_items')
                ->cascadeOnDelete();
        });

        Schema::create('CAT_tech_card_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tech_card_id');
            $table->string('ingredient_ref', 191);
            $table->decimal('quantity', 12, 3);
            $table->string('unit', 64);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tech_card_id')
                ->references('id')
                ->on('CAT_tech_cards')
                ->cascadeOnDelete();

            $table->index(['tech_card_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CAT_tech_card_lines');
        Schema::dropIfExists('CAT_tech_cards');
    }
};
