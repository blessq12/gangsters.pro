<?php

namespace Tests\Unit\Infrastructure\Notifications;

use App\Application\Notifications\Ports\NotificationDeliveryLogger;
use App\Infrastructure\Notifications\Model\SYS_NotificationDelivery;
use App\Infrastructure\Notifications\Repository\EloquentNotificationDeliveryLogger;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class EloquentNotificationDeliveryLoggerTest extends TestCase
{
    protected function tearDown(): void
    {
        if ($this->databaseTableExists('notification_deliveries')) {
            SYS_NotificationDelivery::query()->delete();
        }

        parent::tearDown();
    }

    public function test_truncates_payload_json(): void
    {
        $this->skipUnlessTableExists('notification_deliveries');

        SYS_NotificationDelivery::query()->delete();

        config([
            'notifications.delivery_log.payload_max_length' => 64,
            'notifications.delivery_log.retention_days' => null,
        ]);

        app(NotificationDeliveryLogger::class)->logSent(
            channel: 'mail',
            eventType: 'profile_updated',
            recipient: 'user@example.com',
            payload: ['note' => str_repeat('x', 120)],
        );

        $row = SYS_NotificationDelivery::query()->first();
        $this->assertNotNull($row);
        $this->assertLessThanOrEqual(70, strlen((string) $row->payload_json));
        $this->assertStringEndsWith('…', (string) $row->payload_json);
    }

    public function test_prunes_old_rows_when_retention_configured(): void
    {
        $this->skipUnlessTableExists('notification_deliveries');

        SYS_NotificationDelivery::query()->delete();

        config(['notifications.delivery_log.retention_days' => 30]);

        SYS_NotificationDelivery::query()->create([
            'channel' => 'mail',
            'event_type' => 'registration_welcome',
            'recipient' => 'old@example.com',
            'status' => 'sent',
            'created_at' => now()->subDays(60),
        ]);

        app(EloquentNotificationDeliveryLogger::class)->logSent(
            channel: 'mail',
            eventType: 'registration_welcome',
            recipient: 'new@example.com',
        );

        $this->assertSame(1, SYS_NotificationDelivery::query()->count());
        $this->assertSame('new@example.com', SYS_NotificationDelivery::query()->value('recipient'));
    }

    private function skipUnlessTableExists(string $table): void
    {
        if (! $this->databaseTableExists($table)) {
            $this->markTestSkipped("Нет таблицы `{$table}` — выполни миграции.");
        }
    }

    private function databaseTableExists(string $table): bool
    {
        if (Schema::hasTable($table)) {
            return true;
        }

        $lower = strtolower($table);

        return $lower !== $table && Schema::hasTable($lower);
    }
}
