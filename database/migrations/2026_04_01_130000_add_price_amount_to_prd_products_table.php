<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->nullable()->after('description');
        });

        DB::table('PRD_products')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($products): void {
                foreach ($products as $product) {
                    $resolvedPrice = DB::table('PRD_product_prices')
                        ->where('product_id', $product->id)
                        ->orderByRaw("
                            CASE
                                WHEN customer_status = 'regular' THEN 0
                                WHEN is_default = 1 THEN 1
                                ELSE 2
                            END
                        ")
                        ->orderBy('id')
                        ->value('amount');

                    DB::table('PRD_products')
                        ->where('id', $product->id)
                        ->update([
                            'price' => $resolvedPrice !== null ? (int) round(((int) $resolvedPrice) / 100) : null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('PRD_products', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
