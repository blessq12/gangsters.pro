<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff: сотрудник может быть привязан к одному филиалу (null = вся компания).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('STF_employees') || Schema::hasColumn('STF_employees', 'branch_id')) {
            return;
        }

        Schema::table('STF_employees', function (Blueprint $table): void {
            $table->unsignedBigInteger('branch_id')->nullable()->after('role');
            $table->index('branch_id');
            $table->foreign('branch_id')->references('id')->on('CMP_branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('STF_employees') || ! Schema::hasColumn('STF_employees', 'branch_id')) {
            return;
        }

        Schema::table('STF_employees', function (Blueprint $table): void {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
