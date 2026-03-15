<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('order_id', 36);
            $table->unsignedBigInteger('product_original_id')->nullable()->index();

            $table->string('product_name');
            $table->string('product_sku');
            $table->bigInteger('product_list_price');
            $table->bigInteger('product_final_price');
            $table->json('product_attributes')->nullable();
            $table->json('product_media')->nullable();

            $table->integer('quantity');
            $table->bigInteger('unit_price');
            $table->bigInteger('row_subtotal');
            $table->bigInteger('row_discount');
            $table->bigInteger('row_total');

            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_order_items');
    }
};

