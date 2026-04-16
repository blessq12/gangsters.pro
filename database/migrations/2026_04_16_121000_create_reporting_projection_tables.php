<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_client_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->primary();
            $table->unsignedInteger('addresses_count')->default(0);
            $table->timestamps();
        });

        Schema::create('reporting_client_order_facts', function (Blueprint $table) {
            $table->string('order_id', 36)->primary();
            $table->unsignedBigInteger('client_id')->index();
            $table->string('payment_status')->nullable();
            $table->bigInteger('total')->default(0);
            $table->timestamps();
            $table->index(['client_id', 'created_at']);
        });

        DB::table('UR_clients')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($clients): void {
                $now = now();

                $rows = $clients->map(function (object $client) use ($now): array {
                    $addressesCount = (int) DB::table('UR_client_addresses')
                        ->where('client_id', $client->id)
                        ->whereNull('deleted_at')
                        ->count();

                    return [
                        'client_id' => (int) $client->id,
                        'addresses_count' => $addressesCount,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();

                if ($rows !== []) {
                    DB::table('reporting_client_profiles')->upsert(
                        $rows,
                        ['client_id'],
                        ['addresses_count', 'updated_at'],
                    );
                }
            });

        DB::table('ORD_orders')
            ->whereNotNull('client_id')
            ->orderBy('id')
            ->chunk(100, function ($orders): void {
                $rows = $orders->map(static fn (object $order): array => [
                    'order_id' => (string) $order->id,
                    'client_id' => (int) $order->client_id,
                    'payment_status' => $order->payment_status,
                    'total' => (int) $order->total,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ])->all();

                if ($rows !== []) {
                    DB::table('reporting_client_order_facts')->upsert(
                        $rows,
                        ['order_id'],
                        ['client_id', 'payment_status', 'total', 'created_at', 'updated_at'],
                    );
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_client_order_facts');
        Schema::dropIfExists('reporting_client_profiles');
    }
};
