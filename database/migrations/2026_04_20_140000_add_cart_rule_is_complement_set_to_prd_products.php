<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->boolean('cart_rule_is_complement_set')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->dropColumn('cart_rule_is_complement_set');
        });
    }
};
