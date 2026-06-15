<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ING_partner_sku_bindings', function (Blueprint $table) {
            $table->id();
            $table->string('partner_code', 64);
            $table->string('partner_sku', 128);
            $table->unsignedBigInteger('product_id');
            $table->timestamps();

            $table->unique(['partner_code', 'partner_sku'], 'ing_partner_sku_unique');
            $table->index('product_id');
        });

        Schema::create('ING_ingress_audits', function (Blueprint $table) {
            $table->id();
            $table->string('partner_code', 64);
            $table->string('external_order_id', 128);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('result', 32);
            $table->json('raw_payload');
            $table->timestamp('created_at');

            $table->index(['partner_code', 'external_order_id']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ING_ingress_audits');
        Schema::dropIfExists('ING_partner_sku_bindings');
    }
};
