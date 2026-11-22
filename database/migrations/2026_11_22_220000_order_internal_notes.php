<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Внутренние пометки саппорта по заказу (append-only).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_order_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->text('body');
            $table->unsignedBigInteger('author_employee_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('order_id')
                ->references('id')
                ->on('ORD_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_order_notes');
    }
};
