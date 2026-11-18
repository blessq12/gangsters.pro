<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('PRM_zones')) {
            Schema::create('PRM_zones', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('name');
                $table->unsignedInteger('fee_kopecks')->default(0);
                $table->boolean('is_remote')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('PRM_product_roles')) {
            Schema::create('PRM_product_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('role', 32);
                $table->string('name');
                $table->unsignedInteger('price_kopecks')->default(0);
                $table->unsignedBigInteger('paid_twin_product_id')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'role']);
                $table->index('role');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('PRM_product_roles');
        Schema::dropIfExists('PRM_zones');
    }
};
