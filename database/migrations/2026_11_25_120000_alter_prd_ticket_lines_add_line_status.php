<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_ticket_lines', function (Blueprint $table): void {
            $table->string('status', 32)->default('queued')->after('position');
            $table->unsignedBigInteger('done_by')->nullable()->after('status');
            $table->timestamp('done_at')->nullable()->after('done_by');
            $table->unsignedBigInteger('packed_by')->nullable()->after('done_at');
            $table->timestamp('packed_at')->nullable()->after('packed_by');
        });
    }

    public function down(): void
    {
        Schema::table('PRD_ticket_lines', function (Blueprint $table): void {
            $table->dropColumn(['status', 'done_by', 'done_at', 'packed_by', 'packed_at']);
        });
    }
};
