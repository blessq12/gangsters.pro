<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CHK_checkouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 32);
            $table->json('cart_snapshot');
            $table->json('client_snapshot')->nullable();
            $table->json('delivery_snapshot')->nullable();
            $table->json('payment_snapshot')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CHK_checkouts');
    }
};
