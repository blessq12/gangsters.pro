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
        Schema::create('UR_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->date('birth_date')->nullable();
            $table->string('password')->nullable();
            $table->string('status')->default('active');
            $table->boolean('consent_personal_data')->default(false);
            $table->boolean('consent_marketing')->default(false);
            $table->unsignedBigInteger('default_address_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('UR_clients');
    }
};

