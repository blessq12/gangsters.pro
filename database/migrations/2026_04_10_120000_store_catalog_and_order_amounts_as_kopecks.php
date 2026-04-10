<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ранее PRD_products.price и суммы ORD_* хранились как целые рубли.
 * Переводим в копейки (×100) для точности до 2 знаков в рублях на границе API.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('PRD_products')->whereNotNull('price')->update([
            'price' => DB::raw('`price` * 100'),
        ]);

        DB::table('ORD_orders')->update([
            'subtotal' => DB::raw('`subtotal` * 100'),
            'discount_total' => DB::raw('`discount_total` * 100'),
            'total' => DB::raw('`total` * 100'),
        ]);

        DB::table('ORD_order_items')->update([
            'product_list_price' => DB::raw('`product_list_price` * 100'),
            'product_final_price' => DB::raw('`product_final_price` * 100'),
            'unit_price' => DB::raw('`unit_price` * 100'),
            'row_subtotal' => DB::raw('`row_subtotal` * 100'),
            'row_discount' => DB::raw('`row_discount` * 100'),
            'row_total' => DB::raw('`row_total` * 100'),
        ]);
    }

    public function down(): void
    {
        DB::table('PRD_products')->whereNotNull('price')->update([
            'price' => DB::raw('ROUND(`price` / 100)'),
        ]);

        DB::table('ORD_orders')->update([
            'subtotal' => DB::raw('ROUND(`subtotal` / 100)'),
            'discount_total' => DB::raw('ROUND(`discount_total` / 100)'),
            'total' => DB::raw('ROUND(`total` / 100)'),
        ]);

        DB::table('ORD_order_items')->update([
            'product_list_price' => DB::raw('ROUND(`product_list_price` / 100)'),
            'product_final_price' => DB::raw('ROUND(`product_final_price` / 100)'),
            'unit_price' => DB::raw('ROUND(`unit_price` / 100)'),
            'row_subtotal' => DB::raw('ROUND(`row_subtotal` / 100)'),
            'row_discount' => DB::raw('ROUND(`row_discount` / 100)'),
            'row_total' => DB::raw('ROUND(`row_total` / 100)'),
        ]);
    }
};
