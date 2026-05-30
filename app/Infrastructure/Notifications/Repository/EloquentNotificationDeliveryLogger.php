<?php

namespace App\Infrastructure\Notifications\Repository;

use App\Application\Notifications\Ports\NotificationDeliveryLogger;
use App\Infrastructure\Notifications\Model\SYS_NotificationDelivery;
use Illuminate\Support\Facades\DB;

final class EloquentNotificationDeliveryLogger implements NotificationDeliveryLogger
{
    public function logSent(
        string $channel,
        string $eventType,
        string $recipient,
        array $payload = [],
    ): void {
        $this->persist($channel, $eventType, $recipient, 'sent', null, $payload);
    }

    public function logFailed(
        string $channel,
        string $eventType,
        string $recipient,
        string $errorMessage,
        array $payload = [],
    ): void {
        $this->persist($channel, $eventType, $recipient, 'failed', $errorMessage, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function persist(
        string $channel,
        string $eventType,
        string $recipient,
        string $status,
        ?string $errorMessage,
        array $payload,
    ): void {
        SYS_NotificationDelivery::query()->create([
            'channel' => $channel,
            'event_type' => $eventType,
            'recipient' => $recipient,
            'status' => $status,
            'error_message' => $errorMessage,
            'payload_json' => $this->encodePayload($payload),
            'created_at' => now(),
        ]);

        $this->pruneExpiredIfConfigured();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encodePayload(array $payload): ?string
    {
        if ($payload === []) {
            return null;
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return null;
        }

        $max = (int) config('notifications.delivery_log.payload_max_length', 2000);
        if ($max < 64) {
            $max = 64;
        }
        if (strlen($json) <= $max) {
            return $json;
        }

        return substr($json, 0, $max).'…';
    }

    private function pruneExpiredIfConfigured(): void
    {
        $days = config('notifications.delivery_log.retention_days');
        if (! is_int($days) || $days <= 0) {
            return;
        }

        DB::table('notification_deliveries')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
