<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('kitchen_id')->nullable()->after('client_id');
            $table->unsignedBigInteger('branch_id')->nullable()->after('kitchen_id');
            $table->index('kitchen_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::table('ORD_orders', function (Blueprint $table): void {
            $table->dropIndex(['kitchen_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn(['kitchen_id', 'branch_id']);
        });
    }
};
