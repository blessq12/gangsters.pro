<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('SHP_shopping_cart_rule_settings', function (Blueprint $table) {
            if (Schema::hasColumn('SHP_shopping_cart_rule_settings', 'complement_product_id')) {
                $table->dropColumn('complement_product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('SHP_shopping_cart_rule_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('complement_product_id')->nullable();
        });
    }
};
