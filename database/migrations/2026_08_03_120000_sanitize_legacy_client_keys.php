<?php

use App\Shared\ValueObject\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Чистит «чужие» и осиротевшие client_id в живых таблицах
 * (коллизии старых CLN id с CRM_clients.id без FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->deleteLegacyClientTokens();
        $this->sanitizeOrdOrdersClientIds();
    }

    public function down(): void
    {
        // Данные после санации не восстанавливаем.
    }

    private function deleteLegacyClientTokens(): void
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Infrastructure\\Client\\Model\\CLN_Client')
            ->delete();
    }

    private function sanitizeOrdOrdersClientIds(): void
    {
        if (! Schema::hasTable('ORD_orders') || ! Schema::hasTable('CRM_clients')) {
            return;
        }

        $clientsById = DB::table('CRM_clients')
            ->select(['id', 'phone'])
            ->get()
            ->keyBy('id');

        DB::table('ORD_orders')
            ->whereNotNull('client_id')
            ->orderBy('id')
            ->chunkById(100, function ($orders) use ($clientsById): void {
                foreach ($orders as $order) {
                    $clientId = (int) $order->client_id;
                    $client = $clientsById->get($clientId);

                    if ($client === null) {
                        $this->nullOrdClientId((int) $order->id);

                        continue;
                    }

                    $snapshot = json_decode((string) $order->client_snapshot, true);
                    if (! is_array($snapshot)) {
                        continue;
                    }

                    $snapshotPhoneDigits = PhoneNumber::normalizeDigits($snapshot['phone'] ?? null);
                    $clientPhoneDigits = PhoneNumber::normalizeDigits($client->phone);

                    if (
                        $snapshotPhoneDigits !== ''
                        && $clientPhoneDigits !== ''
                        && $snapshotPhoneDigits !== $clientPhoneDigits
                    ) {
                        $this->nullOrdClientId((int) $order->id);
                    }
                }
            });
    }

    private function nullOrdClientId(int $orderId): void
    {
        DB::table('ORD_orders')
            ->where('id', $orderId)
            ->update(['client_id' => null]);
    }
};
