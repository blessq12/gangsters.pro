<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('CMP_branches')) {
            Schema::create('CMP_branches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('company_id');
            });
        }

        if (! Schema::hasTable('CMP_kitchens')) {
            Schema::create('CMP_kitchens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->string('city')->nullable();
                $table->string('street')->nullable();
                $table->string('house')->nullable();
                $table->text('comment')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();

                $table->index('branch_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('CMP_kitchens');
        Schema::dropIfExists('CMP_branches');
    }
};
