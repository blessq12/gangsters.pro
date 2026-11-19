<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('DLV_route_stops')) {
            return;
        }

        if (! Schema::hasColumn('DLV_route_stops', 'promised_at')) {
            return;
        }

        Schema::table('DLV_route_stops', function (Blueprint $table): void {
            $table->dropColumn('promised_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('DLV_route_stops')) {
            return;
        }

        if (Schema::hasColumn('DLV_route_stops', 'promised_at')) {
            return;
        }

        Schema::table('DLV_route_stops', function (Blueprint $table): void {
            $table->timestamp('promised_at')->nullable()->index();
        });
    }
};
