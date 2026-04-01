<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complimentary_item_rule_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->timestamps();

            $table->unique(
                ['rule_id', 'category_id'],
                'complimentary_item_rule_categories_rule_category_unique'
            );
        });

        $now = now();

        $rows = DB::table('complimentary_item_rules')
            ->select(['id', 'trigger_category_id'])
            ->whereNotNull('trigger_category_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('complimentary_item_rule_categories')->updateOrInsert(
                [
                    'rule_id' => (int) $row->id,
                    'category_id' => (int) $row->trigger_category_id,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('complimentary_item_rule_categories');
    }
};
