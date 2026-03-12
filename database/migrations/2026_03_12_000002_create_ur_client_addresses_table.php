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
        Schema::create('UR_client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('UR_clients')
                ->onDelete('cascade');

            $table->string('type')->default('additional'); // default | additional
            $table->string('title')->nullable();
            $table->string('street');
            $table->string('house');
            $table->string('liter')->nullable();
            $table->string('staircase')->nullable();
            $table->string('apartment')->nullable();
            $table->string('entrance_code')->nullable();
            $table->string('floor')->nullable();
            $table->string('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('UR_client_addresses');
    }
};

