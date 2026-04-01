<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PRD_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->string('color', 20)->default('amber');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('PRD_product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained('PRD_tags')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'tag_id']);
        });

        if (!Schema::hasTable('PRD_product_tags')) {
            return;
        }

        $legacyRows = DB::table('PRD_product_tags')
            ->select('id', 'product_id', 'code')
            ->orderBy('id')
            ->get();

        $codeToId = [];
        $usedCodes = [];

        foreach ($legacyRows as $row) {
            $normalized = Str::of((string) $row->code)
                ->lower()
                ->trim()
                ->replaceMatches('/[^a-z0-9]+/u', '_')
                ->trim('_')
                ->value();

            if ($normalized === '') {
                $normalized = 'tag_' . $row->id;
            }

            $finalCode = $normalized;
            $suffix = 1;
            while (isset($usedCodes[$finalCode]) && $usedCodes[$finalCode] !== $normalized) {
                $suffix++;
                $finalCode = $normalized . '_' . $suffix;
            }

            $usedCodes[$finalCode] = $normalized;

            if (!isset($codeToId[$finalCode])) {
                $label = Str::of($row->code)->replace('_', ' ')->trim()->title()->value();
                $tagId = DB::table('PRD_tags')->insertGetId([
                    'code' => $finalCode,
                    'label' => $label !== '' ? $label : Str::headline($finalCode),
                    'color' => 'amber',
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $codeToId[$finalCode] = $tagId;
            }

            DB::table('PRD_product_tag')->updateOrInsert(
                [
                    'product_id' => $row->product_id,
                    'tag_id' => $codeToId[$finalCode],
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        Schema::drop('PRD_product_tags');
    }

    public function down(): void
    {
        Schema::create('PRD_product_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('PRD_products')
                ->onDelete('cascade');
            $table->string('code');
            $table->timestamps();
        });

        if (Schema::hasTable('PRD_product_tag') && Schema::hasTable('PRD_tags')) {
            $rows = DB::table('PRD_product_tag as pivot')
                ->join('PRD_tags as tags', 'tags.id', '=', 'pivot.tag_id')
                ->select('pivot.product_id', 'tags.code')
                ->get();

            foreach ($rows as $row) {
                DB::table('PRD_product_tags')->insert([
                    'product_id' => $row->product_id,
                    'code' => $row->code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::dropIfExists('PRD_product_tag');
        Schema::dropIfExists('PRD_tags');
    }
};
