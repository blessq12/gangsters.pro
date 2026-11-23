<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CRM_client_addresses', function (Blueprint $table): void {
            if (! Schema::hasColumn('CRM_client_addresses', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('comment');
            }
            if (! Schema::hasColumn('CRM_client_addresses', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('CRM_client_addresses', function (Blueprint $table): void {
            if (Schema::hasColumn('CRM_client_addresses', 'lng')) {
                $table->dropColumn('lng');
            }
            if (Schema::hasColumn('CRM_client_addresses', 'lat')) {
                $table->dropColumn('lat');
            }
        });
    }
};
