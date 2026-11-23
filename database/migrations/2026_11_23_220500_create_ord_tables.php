<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order (ORD_): заказ, линии, заметки, коммерческие правила филиала.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_branch_commercial_rules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id')->unique();
            $table->json('gift_rules');
            $table->json('delivery_benefit');
            $table->json('complement_set_benefit');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
        });

        Schema::create('ORD_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 32)->default('public');
            $table->string('checkout_id')->nullable()->index();
            $table->string('status', 32)->index();

            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->string('client_name');
            $table->string('client_phone', 32);

            $table->string('delivery_type', 16);
            $table->string('delivery_zone_id')->nullable()->index();
            $table->unsignedBigInteger('delivery_fee_kopecks')->default(0);
            $table->string('delivery_street')->nullable();
            $table->string('delivery_house')->nullable();
            $table->string('delivery_entrance')->nullable();
            $table->string('delivery_floor')->nullable();
            $table->string('delivery_apartment')->nullable();
            $table->string('delivery_comment')->nullable();
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->unsignedTinyInteger('persons')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();

            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->string('payment_method', 32)->nullable();
            $table->integer('change_from_rubles')->nullable();
            $table->unsignedBigInteger('total_kopecks')->default(0);

            $table->timestamp('created_at')->useCurrent()->index();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('ORD_order_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->index();
            $table->string('name');
            $table->unsignedBigInteger('unit_price_kopecks');
            $table->unsignedInteger('quantity');
            $table->string('line_type', 32)->index();
            $table->unsignedInteger('position')->default(0);

            $table->foreign('order_id')->references('id')->on('ORD_orders')->cascadeOnDelete();
            $table->index(['order_id', 'position']);
        });

        Schema::create('ORD_order_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->text('body');
            $table->unsignedBigInteger('author_employee_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('order_id')->references('id')->on('ORD_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_order_notes');
        Schema::dropIfExists('ORD_order_lines');
        Schema::dropIfExists('ORD_orders');
        Schema::dropIfExists('ORD_branch_commercial_rules');
    }
};
