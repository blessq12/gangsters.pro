<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRM_configuration', function (Blueprint $table) {
            $table->unsignedSmallInteger('complement_set_max_sets')
                ->default(3)
                ->after('complement_set_rolls_per_set');
        });
    }

    public function down(): void
    {
        Schema::table('PRM_configuration', function (Blueprint $table) {
            $table->dropColumn('complement_set_max_sets');
        });
    }
};
