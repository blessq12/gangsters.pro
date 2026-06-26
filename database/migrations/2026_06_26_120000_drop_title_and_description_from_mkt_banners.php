<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('MKT_banners', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('MKT_banners', function (Blueprint $table) {
            $table->string('title')->default('');
            $table->text('description')->nullable();
        });
    }
};
