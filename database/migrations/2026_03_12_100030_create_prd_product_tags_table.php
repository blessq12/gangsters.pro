<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_product_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->onDelete('cascade');

            $table->string('code'); // spicy, no_onion, kids_friendly, ...

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRD_product_tags');
    }
};

