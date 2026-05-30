<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32);
            $table->string('event_type', 64);
            $table->string('recipient');
            $table->string('status', 16);
            $table->text('error_message')->nullable();
            $table->text('payload_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['channel', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
