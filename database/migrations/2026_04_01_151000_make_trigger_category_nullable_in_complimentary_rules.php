<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complimentary_item_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('trigger_category_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('complimentary_item_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('trigger_category_id')->nullable(false)->change();
        });
    }
};
