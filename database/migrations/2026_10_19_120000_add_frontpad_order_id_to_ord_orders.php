<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            $table->string('frontpad_order_id', 64)->nullable()->after('external_order_id');
            $table->unique('frontpad_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            $table->dropUnique(['frontpad_order_id']);
            $table->dropColumn('frontpad_order_id');
        });
    }
};
