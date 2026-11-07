<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DLV_route_sheets', function (Blueprint $table): void {
            $table->id();
            $table->string('status', 32);
            $table->timestamp('created_at');
        });

        Schema::create('DLV_route_stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('route_sheet_id')->constrained('DLV_route_sheets')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('ORD_orders');
            $table->unsignedInteger('position');
            $table->string('outcome', 32);
            $table->string('reason', 500)->default('');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DLV_route_stops');
        Schema::dropIfExists('DLV_route_sheets');
    }
};
