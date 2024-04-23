<?php

use App\Models\User;
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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignIdFor(User::class)->nullable();
            $table->string('name')->nullable();
            $table->string('tel')->nullable();
            $table->string('street')->nullable();
            $table->string('house')->nullable();
            $table->string('building')->nullable();
            $table->string('staircase')->nullable();
            $table->string('floor')->nullable();
            $table->string('apartment')->nullable();
            $table->string('total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
