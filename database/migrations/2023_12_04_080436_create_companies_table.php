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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            // company data
            $table->string('name');
            $table->string('description');
            $table->string('logo');
            // company address data
            $table->string('country');
            $table->string('state');
            $table->string('city');
            $table->string('street');
            $table->string('house');
            // company contact data
            $table->string('phone');
            $table->string('phone_additional');
            $table->string('email_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
