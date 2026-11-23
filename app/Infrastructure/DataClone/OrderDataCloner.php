<?php

namespace App\Infrastructure\DataClone;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

/**
 * ORD_orders (JSON snapshots) → ORD_orders + ORD_order_lines.
 */
final class OrderDataCloner
{
    /**
     * @return array{orders: int, lines: int, skipped_lines: int}
     */
    public function clone(Connection $source, int $branchId): array
    {
        $orders = $source->table('ORD_orders')->orderBy('id')->get();

        $stats = [
            'orders' => 0,
            'lines' => 0,
            'skipped_lines' => 0,
        ];

        DB::transaction(function () use ($orders, $branchId, &$stats): void {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('ORD_order_notes')->delete();
            DB::table('ORD_order_lines')->delete();
            DB::table('ORD_orders')->delete();

            foreach ($orders as $order) {
                $cart = $this->decodeObject($order->cart_snapshot);
                $client = $this->decodeObject($order->client_snapshot);
                $delivery = $this->decodeObject($order->delivery_snapshot);
                $payment = $this->decodeObject($order->payment_snapshot);

                $clientName = trim((string) ($client['name'] ?? ''));
                $clientPhone = trim((string) ($client['phone'] ?? ''));
                if ($clientName === '') {
                    $clientName = 'Гость';
                }
                if ($clientPhone === '') {
                    $clientPhone = '0000000000';
                }

                $deliveryMethod = (string) ($delivery['method'] ?? 'courier');
                $deliveryType = $deliveryMethod === 'pickup' ? 'pickup' : 'courier';
                $address = is_array($delivery['address'] ?? null) ? $delivery['address'] : [];

                $feeRubles = $delivery['delivery_fee_rubles'] ?? null;
                $deliveryFeeKopecks = $feeRubles !== null
                    ? (int) $feeRubles * 100
                    : 0;

                $orderId = (int) $order->id;
                $clientId = $order->client_id !== null ? (int) $order->client_id : null;
                if ($clientId !== null && ! DB::table('CLI_clients')->where('id', $clientId)->exists()) {
                    $clientId = null;
                }

                DB::table('ORD_orders')->insert([
                    'id' => $orderId,
                    'source' => (string) ($order->source ?: 'site'),
                    'checkout_id' => $order->checkout_id,
                    'status' => $this->mapStatus((string) $order->status),
                    'client_id' => $clientId,
                    'client_name' => mb_substr($clientName, 0, 255),
                    'client_phone' => mb_substr($clientPhone, 0, 32),
                    'delivery_type' => $deliveryType,
                    'delivery_zone_id' => null,
                    'delivery_fee_kopecks' => $deliveryFeeKopecks,
                    'delivery_street' => $this->nullableString($address['street'] ?? null, 255),
                    'delivery_house' => $this->nullableString($address['house'] ?? null, 255),
                    'delivery_entrance' => $this->nullableString($address['entrance'] ?? null, 255),
                    'delivery_floor' => $this->nullableString($address['floor'] ?? null, 255),
                    'delivery_apartment' => $this->nullableString($address['apartment'] ?? null, 255),
                    'delivery_comment' => $this->nullableString($delivery['comment'] ?? null, 255),
                    'delivery_lat' => isset($address['lat']) ? (float) $address['lat'] : null,
                    'delivery_lng' => isset($address['lng']) ? (float) $address['lng'] : null,
                    'persons' => isset($delivery['persons']) ? (int) $delivery['persons'] : null,
                    'scheduled_at' => $this->nullableString($delivery['scheduled_at'] ?? null),
                    'branch_id' => $branchId,
                    'payment_id' => null,
                    'payment_method' => $this->nullableString($payment['method'] ?? null, 32),
                    'change_from_rubles' => isset($payment['change_from_rubles'])
                        ? (int) $payment['change_from_rubles']
                        : null,
                    'total_kopecks' => (int) $order->total_rubles * 100,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->created_at,
                ]);
                $stats['orders']++;

                $position = 0;
                $lines = is_array($cart['lines'] ?? null) ? $cart['lines'] : [];
                foreach ($lines as $line) {
                    if (! is_array($line)) {
                        $stats['skipped_lines']++;

                        continue;
                    }

                    $mapped = $this->mapLine($line, $position);
                    if ($mapped === null) {
                        $stats['skipped_lines']++;

                        continue;
                    }

                    DB::table('ORD_order_lines')->insert($mapped + ['order_id' => $orderId]);
                    $position++;
                    $stats['lines']++;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array{
     *     product_id: int,
     *     name: string,
     *     unit_price_kopecks: int,
     *     quantity: int,
     *     line_type: string,
     *     position: int
     * }|null
     */
    private function mapLine(array $line, int $position): ?array
    {
        $payload = is_array($line['payload'] ?? null) ? $line['payload'] : [];
        $kind = (string) ($payload['kind'] ?? 'user');

        if ($kind === 'delivery') {
            return null;
        }

        $lineType = match ($kind) {
            'gift' => 'gift',
            'complement' => 'complement',
            'user' => (($payload['catalog_kind'] ?? null) === 'set') ? 'set' : 'regular',
            default => 'regular',
        };

        $quantity = max(1, (int) ($line['quantity'] ?? 1));
        $unitPriceRubles = $line['unit_price_rubles'] ?? null;
        if ($unitPriceRubles === null && isset($line['line_total_rubles'])) {
            $unitPriceRubles = (int) round(((int) $line['line_total_rubles']) / $quantity);
        }

        $productId = (int) ($line['product_id'] ?? 0);
        $name = trim((string) ($line['product_name'] ?? ''));
        if ($productId <= 0 || $name === '') {
            return null;
        }

        return [
            'product_id' => $productId,
            'name' => mb_substr($name, 0, 255),
            'unit_price_kopecks' => (int) ($unitPriceRubles ?? 0) * 100,
            'quantity' => $quantity,
            'line_type' => $lineType,
            'position' => $position,
        ];
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'new', 'preorder', 'confirmed', 'preparing', 'ready',
            'assigned_to_route', 'in_transit', 'delivered', 'written_off', 'cancelled' => $status,
            default => 'new',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeObject(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function nullableString(mixed $value, ?int $maxLength = null): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        if ($maxLength !== null) {
            $string = mb_substr($string, 0, $maxLength);
        }

        return $string;
    }
}
