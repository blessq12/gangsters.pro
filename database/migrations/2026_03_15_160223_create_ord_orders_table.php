<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_orders', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->unsignedBigInteger('client_id')->index();

            $table->string('status')->index();

            $table->bigInteger('subtotal');
            $table->bigInteger('discount_total');
            $table->bigInteger('total');

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->json('customer_address')->nullable();

            $table->string('delivery_method')->nullable();
            $table->json('delivery_address')->nullable();
            $table->text('delivery_comment')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('payment_external_id')->nullable();
            $table->string('payment_status')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_orders');
    }
};

