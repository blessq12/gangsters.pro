<?php

namespace App\Application\Notifications\Query;

use App\Application\Notifications\Contracts\NotificationDeliveryReadRepository;
use App\Support\Notifications\NotificationDeliveryLabels;

final class GetAdminNotificationDeliveryListQuery
{
    public function __construct(
        private readonly NotificationDeliveryReadRepository $deliveries,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, per_page: int}
     */
    public function execute(
        ?string $channel = null,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $result = $this->deliveries->paginate(
            channel: $channel,
            status: $status,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            search: $search,
            page: $page,
            perPage: $perPage,
        );

        return [
            'items' => array_map(
                fn (array $row): array => $this->present($row),
                $result['items'],
            ),
            'total' => $result['total'],
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function present(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'channel' => (string) $row['channel'],
            'channel_label' => NotificationDeliveryLabels::channelLabel($row['channel'] ?? null),
            'event_type' => (string) $row['event_type'],
            'event_type_label' => NotificationDeliveryLabels::eventTypeLabel($row['event_type'] ?? null),
            'recipient' => (string) $row['recipient'],
            'status' => (string) $row['status'],
            'status_label' => NotificationDeliveryLabels::statusLabel($row['status'] ?? null),
            'error_message' => filled($row['error_message'] ?? null) ? (string) $row['error_message'] : null,
            'payload_json' => filled($row['payload_json'] ?? null) ? (string) $row['payload_json'] : null,
            'created_at' => (string) $row['created_at'],
        ];
    }
}
