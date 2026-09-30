<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('CMP_company_documents', function (Blueprint $table) {
            $table->renameColumn('key', 'slug');
            $table->renameColumn('title', 'name');
        });
    }

    public function down(): void
    {
        Schema::table('CMP_company_documents', function (Blueprint $table) {
            $table->renameColumn('slug', 'key');
            $table->renameColumn('name', 'title');
        });
    }
};
