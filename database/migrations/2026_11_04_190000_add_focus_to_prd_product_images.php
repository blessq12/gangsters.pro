<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PRD_product_images')) {
            return;
        }

        Schema::table('PRD_product_images', function (Blueprint $table): void {
            if (! Schema::hasColumn('PRD_product_images', 'focus_x')) {
                $table->decimal('focus_x', 5, 2)->nullable()->after('sort_order');
            }

            if (! Schema::hasColumn('PRD_product_images', 'focus_y')) {
                $table->decimal('focus_y', 5, 2)->nullable()->after('focus_x');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('PRD_product_images')) {
            return;
        }

        Schema::table('PRD_product_images', function (Blueprint $table): void {
            if (Schema::hasColumn('PRD_product_images', 'focus_x')) {
                $table->dropColumn('focus_x');
            }

            if (Schema::hasColumn('PRD_product_images', 'focus_y')) {
                $table->dropColumn('focus_y');
            }
        });
    }
};
