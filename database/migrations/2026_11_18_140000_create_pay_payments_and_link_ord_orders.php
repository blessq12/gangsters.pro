<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PAY_payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->string('method', 32);
            $table->unsignedInteger('change_from_rubles')->nullable();
            $table->unsignedBigInteger('amount_kopecks');
            $table->string('status', 32)->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });

        Schema::table('ORD_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('payment_id')->nullable()->after('branch_id');
            $table->index('payment_id');
            $table->dropColumn('payment_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            $table->json('payment_snapshot')->nullable()->after('delivery_snapshot');
            $table->dropIndex(['payment_id']);
            $table->dropColumn('payment_id');
        });

        Schema::dropIfExists('PAY_payments');
    }
};
