<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('eatsId', 255)->nullable()->after('id');
            $table->string('restaurantId', 255)->nullable()->after('eatsId');
            $table->decimal('latitude', 10, 6)->nullable()->after('apartment');
            $table->decimal('longitude', 10, 6)->nullable()->after('latitude');
            $table->dateTime('deliveryDate', 6)->nullable()->after('longitude');
            $table->string('deliveryType', 255)->nullable()->after('deliveryDate');
            $table->decimal('itemsCost', 10, 2)->nullable()->after('total');
            $table->decimal('deliveryFee', 10, 2)->nullable()->after('itemsCost');
            $table->decimal('change', 10, 2)->nullable()->after('deliveryFee');
            $table->text('promos')->nullable()->after('change');
            $table->text('deliveryAddress')->nullable()->after('apartment');
            $table->decimal('total', 10, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {});
    }
};
