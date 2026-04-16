<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('complimentary_item_rule_categories');
        Schema::dropIfExists('complimentary_item_rules');
    }

    public function down(): void
    {
        // Таблицы вертикали Promotions намеренно не восстанавливаются.
    }
};

