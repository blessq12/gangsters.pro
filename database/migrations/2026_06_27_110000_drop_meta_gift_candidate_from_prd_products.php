<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->dropColumn('meta_gift_candidate');
        });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->boolean('meta_gift_candidate')->default(false)->after('meta_counts_as_roll');
        });
    }
};
