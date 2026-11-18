<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('KIT_tickets', function (Blueprint $table): void {
            $table->unsignedBigInteger('kitchen_id')->after('order_id');
            $table->index('kitchen_id');
        });
    }

    public function down(): void
    {
        Schema::table('KIT_tickets', function (Blueprint $table): void {
            $table->dropIndex(['kitchen_id']);
            $table->dropColumn('kitchen_id');
        });
    }
};
