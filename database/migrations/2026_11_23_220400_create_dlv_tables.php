<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Delivery (DLV_): тариф, зоны, маршруты. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DLV_configurations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id')->unique();
            $table->unsignedBigInteger('min_order_amount_kopecks')->nullable();
            $table->unsignedInteger('average_delivery_time_minutes')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
        });

        Schema::create('DLV_zones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('configuration_id');
            $table->string('zone_key');
            $table->string('name');
            $table->unsignedBigInteger('fee_kopecks')->default(0);
            $table->boolean('is_remote')->default(false);
            $table->json('geojson')->nullable();
            $table->unsignedBigInteger('delivery_product_id')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('configuration_id')->references('id')->on('DLV_configurations')->cascadeOnDelete();
            $table->unique(['configuration_id', 'zone_key']);
        });

        Schema::create('DLV_route_sheets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('status', 32)->index();
            $table->unsignedBigInteger('courier_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('DLV_route_stops', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('route_sheet_id');
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedInteger('position');
            $table->string('status', 32)->index();
            $table->string('settlement_note')->nullable();
            $table->timestamps();

            $table->foreign('route_sheet_id')->references('id')->on('DLV_route_sheets')->cascadeOnDelete();
            $table->index(['route_sheet_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DLV_route_stops');
        Schema::dropIfExists('DLV_route_sheets');
        Schema::dropIfExists('DLV_zones');
        Schema::dropIfExists('DLV_configurations');
    }
};
