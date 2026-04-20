<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('SHP_shopping_cart_rule_settings', function (Blueprint $table) {
            if (Schema::hasColumn('SHP_shopping_cart_rule_settings', 'roll_tag')) {
                $table->dropColumn('roll_tag');
            }
            if (Schema::hasColumn('SHP_shopping_cart_rule_settings', 'gift_candidate_tag')) {
                $table->dropColumn('gift_candidate_tag');
            }
        });
    }

    public function down(): void
    {
        Schema::table('SHP_shopping_cart_rule_settings', function (Blueprint $table) {
            $table->string('roll_tag', 64)->default('ROLL');
            $table->string('gift_candidate_tag', 64)->default('GIFT_ROLL');
        });
    }
};
