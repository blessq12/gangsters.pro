<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('checkout_id')->unique();
            $table->string('status', 32);
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedInteger('total_rubles');
            $table->json('cart_snapshot');
            $table->json('client_snapshot');
            $table->json('delivery_snapshot');
            $table->json('payment_snapshot');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_orders');
    }
};
