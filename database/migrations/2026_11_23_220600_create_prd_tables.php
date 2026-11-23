<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Production (PRD_): кухонные тикеты. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_tickets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('assignee_employee_id')->nullable()->index();
            $table->string('status', 32)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('PRD_ticket_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('product_id')->index();
            $table->string('name');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('position')->default(0);

            $table->foreign('ticket_id')->references('id')->on('PRD_tickets')->cascadeOnDelete();
            $table->index(['ticket_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRD_ticket_lines');
        Schema::dropIfExists('PRD_tickets');
    }
};
