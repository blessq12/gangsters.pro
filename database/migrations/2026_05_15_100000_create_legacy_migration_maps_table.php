<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_migration_maps', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 64);
            $table->string('legacy_key', 64);
            $table->string('target_key', 64);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'legacy_key']);
            $table->index(['entity_type', 'target_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_migration_maps');
    }
};
