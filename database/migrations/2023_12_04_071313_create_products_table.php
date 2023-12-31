<?php

use App\Models\ProductCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->boolean('visible')->default(true);
            $table->foreignIdFor(ProductCategory::class);
            $table->string('name')->default('Название не задано');
            $table->boolean('hit')->default(false);
            $table->boolean('spicy')->default(false);
            $table->boolean('kidsAllow')->default(false);
            $table->boolean('onion')->default(false);
            $table->boolean('garlic')->default(false);
            $table->string('consist');
            $table->string('weight');
            $table->string('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
