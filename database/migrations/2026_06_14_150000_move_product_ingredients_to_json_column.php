<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('PRD_products', 'ingredients')) {
            Schema::table('PRD_products', function (Blueprint $table): void {
                $table->json('ingredients')->nullable()->after('nutrition_basis');
            });
        }

        if (Schema::hasTable('PRD_product_ingredients')) {
            $ingredientsByProduct = DB::table('PRD_product_ingredients')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy('product_id');

            foreach ($ingredientsByProduct as $productId => $rows) {
                $names = $rows
                    ->pluck('name')
                    ->map(fn (mixed $name): string => trim((string) $name))
                    ->filter(fn (string $name): bool => $name !== '')
                    ->values()
                    ->all();

                DB::table('PRD_products')
                    ->where('id', $productId)
                    ->update(['ingredients' => json_encode($names, JSON_UNESCAPED_UNICODE)]);
            }

            Schema::dropIfExists('PRD_product_ingredients');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('PRD_product_ingredients')) {
            Schema::create('PRD_product_ingredients', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')
                    ->constrained('PRD_products')
                    ->cascadeOnDelete();
                $table->string('name');
                $table->string('amount')->nullable();
                $table->string('unit')->nullable();
                $table->boolean('is_allergen')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasColumn('PRD_products', 'ingredients')) {
            Schema::table('PRD_products', function (Blueprint $table): void {
                $table->dropColumn('ingredients');
            });
        }
    }
};
