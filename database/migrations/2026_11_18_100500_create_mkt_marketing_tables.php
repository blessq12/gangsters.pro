<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MKT_banners', function (Blueprint $table): void {
            $table->id();
            $table->string('image_desktop')->nullable();
            $table->string('image_mobile')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('MKT_promotions', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('MKT_contact_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });

        Schema::create('MKT_delivery_conditions', function (Blueprint $table): void {
            $table->id();
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MKT_delivery_conditions');
        Schema::dropIfExists('MKT_contact_blocks');
        Schema::dropIfExists('MKT_promotions');
        Schema::dropIfExists('MKT_banners');
    }
};
