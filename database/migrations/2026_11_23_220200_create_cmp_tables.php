<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Company (CMP_): компания, филиал, меню филиала. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CMP_companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('socials')->nullable();
            $table->timestamps();
        });

        Schema::create('CMP_company_legal', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('full_name')->nullable();
            $table->string('inn')->nullable();
            $table->string('ogrn')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('CMP_companies')->cascadeOnDelete();
        });

        Schema::create('CMP_company_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('slug');
            $table->string('name');
            $table->longText('content')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('CMP_companies')->cascadeOnDelete();
            $table->unique(['company_id', 'slug']);
        });

        Schema::create('CMP_branches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->json('schedule')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('house')->nullable();
            $table->string('comment')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 32)->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('CMP_companies')->cascadeOnDelete();
        });

        Schema::create('CMP_branch_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('catalog_item_id');
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('price_kopecks');
            $table->timestamps();

            $table->unique(['branch_id', 'catalog_item_id']);
            $table->index('branch_id');
            $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
            $table->foreign('catalog_item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
        });

        Schema::create('CMP_branch_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_accompanying')->default(false);
            $table->boolean('is_complement')->default(false);
            $table->boolean('is_gift')->default(false);
            $table->timestamps();

            $table->index('branch_id');
            $table->unique(['branch_id', 'slug']);
            $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
        });

        Schema::create('CMP_branch_category_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('entry_kind', 16);
            $table->unsignedBigInteger('entry_id');
            $table->unsignedInteger('sort_order')->default(0);

            $table->foreign('category_id')->references('id')->on('CMP_branch_categories')->cascadeOnDelete();
            $table->unique(['category_id', 'entry_kind', 'entry_id'], 'cmp_branch_cat_entry_uq');
            $table->index(['entry_kind', 'entry_id']);
        });

        Schema::create('CMP_branch_tags', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('code');
            $table->string('label');
            $table->string('color', 32);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('branch_id');
            $table->unique(['branch_id', 'code']);
            $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
        });

        Schema::create('CMP_branch_entry_tags', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tag_id');
            $table->string('entry_kind', 16);
            $table->unsignedBigInteger('entry_id');

            $table->foreign('tag_id')->references('id')->on('CMP_branch_tags')->cascadeOnDelete();
            $table->unique(['tag_id', 'entry_kind', 'entry_id'], 'cmp_branch_entry_tag_uq');
            $table->index(['entry_kind', 'entry_id']);
        });

        Schema::create('CMP_branch_sets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('price_kopecks')->default(0);
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index('branch_id');
            $table->unique(['branch_id', 'slug']);
            $table->foreign('branch_id')->references('id')->on('CMP_branches')->cascadeOnDelete();
        });

        Schema::create('CMP_branch_set_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('set_id');
            $table->unsignedBigInteger('catalog_item_id');
            $table->unsignedInteger('quantity');

            $table->foreign('set_id')->references('id')->on('CMP_branch_sets')->cascadeOnDelete();
            $table->foreign('catalog_item_id')->references('id')->on('CAT_items')->cascadeOnDelete();
            $table->unique(['set_id', 'catalog_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CMP_branch_set_lines');
        Schema::dropIfExists('CMP_branch_sets');
        Schema::dropIfExists('CMP_branch_entry_tags');
        Schema::dropIfExists('CMP_branch_tags');
        Schema::dropIfExists('CMP_branch_category_items');
        Schema::dropIfExists('CMP_branch_categories');
        Schema::dropIfExists('CMP_branch_menu_items');
        Schema::dropIfExists('CMP_branches');
        Schema::dropIfExists('CMP_company_documents');
        Schema::dropIfExists('CMP_company_legal');
        Schema::dropIfExists('CMP_companies');
    }
};
