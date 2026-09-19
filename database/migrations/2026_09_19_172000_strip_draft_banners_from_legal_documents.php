<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('legal_documents')) {
            return;
        }

        $banner = '<p class="bg-amber-50 border-l-4 border-amber-500 p-4 text-amber-800"><strong>Черновик.</strong> Требует юридической вычитки.</p>';

        $rows = DB::table('legal_documents')->select('id', 'body_html')->get();

        foreach ($rows as $row) {
            $html = (string) $row->body_html;
            $cleaned = str_replace($banner, '', $html);
            $cleaned = preg_replace(
                '/\s*<p class="bg-amber-50[^>]*>.*?Черновик.*?<\/p>\s*/su',
                '',
                $cleaned
            ) ?? $cleaned;
            $cleaned = preg_replace(
                '/Черновик[^.]*\.\s*Требует юридической вычитки[^.]*\.\s*/u',
                '',
                $cleaned
            ) ?? $cleaned;
            $cleaned = preg_replace(
                '/Черновик[^.]*\.\s*/u',
                '',
                $cleaned
            ) ?? $cleaned;

            if ($cleaned !== $html) {
                DB::table('legal_documents')->where('id', $row->id)->update([
                    'body_html' => $cleaned,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // необратимо: плашку «Черновик» не восстанавливаем
    }
};
