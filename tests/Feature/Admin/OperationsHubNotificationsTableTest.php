<?php

namespace Tests\Feature\Admin;

use App\Filament\Support\AdminNotificationDeliveryTableQuery;
use App\Filament\Operations\Tables\HubNotificationsTable;
use App\Infrastructure\Notifications\Model\SYS_NotificationDelivery;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

final class OperationsHubNotificationsTableTest extends TestCase
{
    public function test_hub_notifications_table_is_registered(): void
    {
        $component = Livewire::new('app.filament.operations.tables.hub-notifications-table');

        $this->assertInstanceOf(HubNotificationsTable::class, $component);
    }

    public function test_list_query_returns_persisted_delivery_rows(): void
    {
        if (! Schema::hasTable('notification_deliveries')) {
            $this->markTestSkipped('Нет таблицы notification_deliveries — выполни миграции.');
        }

        SYS_NotificationDelivery::query()->delete();

        SYS_NotificationDelivery::query()->create([
            'channel' => 'mail',
            'event_type' => 'order_created',
            'recipient' => 'client@example.com',
            'status' => 'sent',
            'error_message' => null,
            'payload_json' => '{"order_id":"ord-1"}',
            'created_at' => now(),
        ]);

        SYS_NotificationDelivery::query()->create([
            'channel' => 'telegram',
            'event_type' => 'password_reset',
            'recipient' => 'client@example.com',
            'status' => 'failed',
            'error_message' => 'chat not found',
            'payload_json' => '{"has_token":true}',
            'created_at' => now(),
        ]);

        $paginator = app(AdminNotificationDeliveryTableQuery::class)->paginate(
            channel: 'mail',
            status: null,
            dateFrom: null,
            dateTo: null,
            search: null,
            page: 1,
            perPage: 25,
            pageName: 'page',
        );

        $this->assertSame(1, $paginator->total());
        $first = collect($paginator->items())->first();
        $this->assertSame('mail', $first['channel']);
        $this->assertSame('Email', $first['channel_label']);
        $this->assertSame('order_created', $first['event_type']);
        $this->assertSame('Отправлено', $first['status_label']);

        SYS_NotificationDelivery::query()->delete();
    }
}
