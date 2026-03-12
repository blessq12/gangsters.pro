<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('UR_clients', function (Blueprint $table) {
            $table->string('password_reset_token', 100)->nullable()->after('password');
            $table->timestamp('password_reset_requested_at')->nullable()->after('password_reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('UR_clients', function (Blueprint $table) {
            $table->dropColumn(['password_reset_token', 'password_reset_requested_at']);
        });
    }
};

