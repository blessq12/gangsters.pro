<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Телефон филиала — свой контакт точки, не компании.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('CMP_branches')) {
            return;
        }

        if (! Schema::hasColumn('CMP_branches', 'phone')) {
            Schema::table('CMP_branches', function (Blueprint $table): void {
                $table->string('phone', 32)->nullable()->after('longitude');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('CMP_branches')) {
            return;
        }

        if (Schema::hasColumn('CMP_branches', 'phone')) {
            Schema::table('CMP_branches', function (Blueprint $table): void {
                $table->dropColumn('phone');
            });
        }
    }
};
