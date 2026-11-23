<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Кухня: закрепление started-тикета за сотрудником (assignee).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('KIT_tickets', function (Blueprint $table): void {
            $table->unsignedBigInteger('assignee_employee_id')->nullable()->after('branch_id');
            $table->index('assignee_employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('KIT_tickets', function (Blueprint $table): void {
            $table->dropIndex(['assignee_employee_id']);
            $table->dropColumn('assignee_employee_id');
        });
    }
};
