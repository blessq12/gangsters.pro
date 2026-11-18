<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 32);
            $table->string('checkout_id')->nullable()->index();
            $table->string('partner_code')->nullable();
            $table->string('external_order_id')->nullable()->index();
            $table->string('status', 32)->index();
            $table->json('cart_snapshot');
            $table->json('client_snapshot');
            $table->json('delivery_snapshot');
            $table->json('payment_snapshot');
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->unsignedBigInteger('total_kopecks')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_orders');
    }
};
