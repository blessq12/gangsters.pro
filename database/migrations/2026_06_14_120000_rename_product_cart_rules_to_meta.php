<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PRD_products')) {
            return;
        }

        Schema::table('PRD_products', function (Blueprint $table): void {
            if (Schema::hasColumn('PRD_products', 'cart_rule_counts_as_roll')) {
                $table->renameColumn('cart_rule_counts_as_roll', 'meta_counts_as_roll');
            }

            if (Schema::hasColumn('PRD_products', 'cart_rule_gift_candidate')) {
                $table->renameColumn('cart_rule_gift_candidate', 'meta_gift_candidate');
            }

            if (Schema::hasColumn('PRD_products', 'cart_rule_is_complement_set')) {
                $table->renameColumn('cart_rule_is_complement_set', 'meta_is_complement_set');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('PRD_products')) {
            return;
        }

        Schema::table('PRD_products', function (Blueprint $table): void {
            if (Schema::hasColumn('PRD_products', 'meta_counts_as_roll')) {
                $table->renameColumn('meta_counts_as_roll', 'cart_rule_counts_as_roll');
            }

            if (Schema::hasColumn('PRD_products', 'meta_gift_candidate')) {
                $table->renameColumn('meta_gift_candidate', 'cart_rule_gift_candidate');
            }

            if (Schema::hasColumn('PRD_products', 'meta_is_complement_set')) {
                $table->renameColumn('meta_is_complement_set', 'cart_rule_is_complement_set');
            }
        });
    }
};
