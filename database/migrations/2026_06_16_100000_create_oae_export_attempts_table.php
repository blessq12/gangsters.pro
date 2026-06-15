<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('OAE_export_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('system_code', 64);
            $table->string('status', 32);
            $table->unsignedInteger('attempt')->default(1);
            $table->string('external_reference', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at');

            $table->index(['order_id', 'system_code']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('OAE_export_attempts');
    }
};
