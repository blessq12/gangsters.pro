<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DLV_configuration', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('min_order_amount_kopecks')->nullable();
            $table->unsignedBigInteger('delivery_fee_kopecks')->nullable();
            $table->unsignedBigInteger('outside_zone_delivery_fee_kopecks')->nullable();
            $table->unsignedSmallInteger('average_delivery_time_minutes')->nullable();
            $table->string('kitchen_city')->nullable();
            $table->string('kitchen_street')->nullable();
            $table->string('kitchen_house')->nullable();
            $table->string('kitchen_address_comment')->nullable();
            $table->text('kitchen_address')->nullable()->comment('Строка для геокодера на карте');
            $table->decimal('kitchen_latitude', 10, 7)->nullable();
            $table->decimal('kitchen_longitude', 10, 7)->nullable();
            $table->json('delivery_zone_geojson')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DLV_configuration');
    }
};
