<?php

use App\Repositories\LegalDocumentRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('legal_documents')) {
            return;
        }

        app(LegalDocumentRepository::class)->syncAllCurrentFlags();
    }

    public function down(): void
    {
        //
    }
};
