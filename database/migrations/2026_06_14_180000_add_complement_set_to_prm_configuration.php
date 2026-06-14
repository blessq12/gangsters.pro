<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRM_configuration', function (Blueprint $table) {
            $table->boolean('complement_set_benefit_active')->default(true)->after('delivery_benefit_active');
            $table->unsignedSmallInteger('complement_set_rolls_per_set')->default(2)->after('complement_set_benefit_active');
        });
    }

    public function down(): void
    {
        Schema::table('PRM_configuration', function (Blueprint $table) {
            $table->dropColumn([
                'complement_set_benefit_active',
                'complement_set_rolls_per_set',
            ]);
        });
    }
};
