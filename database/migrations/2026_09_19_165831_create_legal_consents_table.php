<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('legal_document_id')->constrained('legal_documents')->restrictOnDelete();
            $table->string('consent_type');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
            $table->index('legal_document_id');
            $table->index('consent_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_consents');
    }
};
