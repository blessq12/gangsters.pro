<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ORD_order_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('author_employee_id')->nullable()->index();
            $table->json('changes');
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('order_id')->references('id')->on('ORD_orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ORD_order_audits');
    }
};
