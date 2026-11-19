<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supporting/Payment: платёжное поручение (копейки, статусы pending/paid/refunded/cancelled).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PAY_payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->string('method', 32);
            $table->integer('change_from_rubles')->nullable();
            $table->unsignedBigInteger('amount_kopecks');
            $table->string('status', 32)->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PAY_payments');
    }
};
