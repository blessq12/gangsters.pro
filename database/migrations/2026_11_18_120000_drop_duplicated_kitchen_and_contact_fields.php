<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('DLV_configurations')) {
            Schema::table('DLV_configurations', function (Blueprint $table): void {
                if (Schema::hasColumn('DLV_configurations', 'kitchen_address')) {
                    $table->dropColumn('kitchen_address');
                }
                if (Schema::hasColumn('DLV_configurations', 'kitchen_latitude')) {
                    $table->dropColumn('kitchen_latitude');
                }
                if (Schema::hasColumn('DLV_configurations', 'kitchen_longitude')) {
                    $table->dropColumn('kitchen_longitude');
                }
            });
        }

        Schema::dropIfExists('MKT_contact_blocks');
    }

    public function down(): void
    {
        if (Schema::hasTable('DLV_configurations')) {
            Schema::table('DLV_configurations', function (Blueprint $table): void {
                if (! Schema::hasColumn('DLV_configurations', 'kitchen_address')) {
                    $table->json('kitchen_address')->nullable();
                }
                if (! Schema::hasColumn('DLV_configurations', 'kitchen_latitude')) {
                    $table->decimal('kitchen_latitude', 10, 7)->nullable();
                }
                if (! Schema::hasColumn('DLV_configurations', 'kitchen_longitude')) {
                    $table->decimal('kitchen_longitude', 10, 7)->nullable();
                }
            });
        }

        if (! Schema::hasTable('MKT_contact_blocks')) {
            Schema::create('MKT_contact_blocks', function (Blueprint $table): void {
                $table->id();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->json('extra')->nullable();
                $table->timestamps();
            });
        }
    }
};
