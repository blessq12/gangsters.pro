<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SHP_shopping_cart_rule_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('complement_rule_enabled')->default(true);
            $table->boolean('gift_rule_enabled')->default(true);
            $table->unsignedBigInteger('complement_product_id')->nullable();
            $table->unsignedInteger('gift_threshold_kopecks')->default(180_000);
            $table->string('roll_tag', 64)->default('ROLL');
            $table->string('gift_candidate_tag', 64)->default('GIFT_ROLL');
            $table->unsignedTinyInteger('rolls_per_complement')->default(2);
            $table->unsignedSmallInteger('complement_rule_sort')->default(10);
            $table->unsignedSmallInteger('gift_rule_sort')->default(20);
            $table->timestamps();
        });

        DB::table('SHP_shopping_cart_rule_settings')->insert([
            'id' => 1,
            'complement_rule_enabled' => true,
            'gift_rule_enabled' => true,
            'complement_product_id' => null,
            'gift_threshold_kopecks' => 180_000,
            'roll_tag' => 'ROLL',
            'gift_candidate_tag' => 'GIFT_ROLL',
            'rolls_per_complement' => 2,
            'complement_rule_sort' => 10,
            'gift_rule_sort' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('SHP_shopping_cart_rule_settings');
    }
};
