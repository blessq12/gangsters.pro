<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRM_configuration', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gift_pickup_min_order_kopecks')->nullable();
            $table->unsignedBigInteger('gift_courier_min_order_kopecks')->nullable();
            $table->boolean('gift_benefit_active')->default(true);
            $table->unsignedBigInteger('delivery_free_threshold_kopecks')->nullable();
            $table->unsignedBigInteger('delivery_outside_zone_surcharge_kopecks')->nullable();
            $table->boolean('delivery_benefit_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRM_configuration');
    }
};
