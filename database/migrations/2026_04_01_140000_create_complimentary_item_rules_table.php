<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complimentary_item_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trigger_category_id')->index();
            $table->unsignedBigInteger('gift_product_id')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();

            $table->unique(
                ['trigger_category_id', 'gift_product_id'],
                'complimentary_item_rules_trigger_gift_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complimentary_item_rules');
    }
};
