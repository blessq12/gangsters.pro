<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRM_policies', function (Blueprint $table): void {
            $table->id();
            $table->json('gift_rules');
            $table->json('delivery_benefit');
            $table->json('complement_set_benefit');
            $table->timestamps();
        });

        Schema::create('PRM_product_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('catalog_item_id')->index();
            $table->string('role', 32);
            $table->string('name_snapshot');
            $table->unsignedBigInteger('price_kopecks_snapshot');
            $table->unsignedBigInteger('paid_twin_catalog_item_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['catalog_item_id', 'role']);
        });

        Schema::create('PRM_zones', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->unsignedBigInteger('fee_kopecks')->default(0);
            $table->boolean('is_remote')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PRM_zones');
        Schema::dropIfExists('PRM_product_roles');
        Schema::dropIfExists('PRM_policies');
    }
};
