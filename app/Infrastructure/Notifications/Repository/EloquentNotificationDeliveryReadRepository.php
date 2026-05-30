<?php

namespace App\Infrastructure\Notifications\Repository;

use App\Application\Notifications\Contracts\NotificationDeliveryReadRepository;
use App\Infrastructure\Notifications\Model\SYS_NotificationDelivery;
use Illuminate\Database\Eloquent\Builder;

final class EloquentNotificationDeliveryReadRepository implements NotificationDeliveryReadRepository
{
    public function paginate(
        ?string $channel = null,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $query = SYS_NotificationDelivery::query()->orderByDesc('created_at');

        if (filled($channel)) {
            $query->where('channel', $channel);
        }

        if (filled($status)) {
            $query->where('status', $status);
        }

        if (filled($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (filled($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (filled($search)) {
            $this->applySearchFilter($query, $search);
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = [];
        foreach ($paginator->items() as $model) {
            /** @var SYS_NotificationDelivery $model */
            $items[] = [
                'id' => (int) $model->id,
                'channel' => (string) $model->channel,
                'event_type' => (string) $model->event_type,
                'recipient' => (string) $model->recipient,
                'status' => (string) $model->status,
                'error_message' => $model->error_message,
                'payload_json' => $model->payload_json,
                'created_at' => $model->created_at?->toIso8601String() ?? '',
            ];
        }

        return [
            'items' => $items,
            'total' => $paginator->total(),
        ];
    }

    /**
     * @param  Builder<SYS_NotificationDelivery>  $query
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $term = '%'.addcslashes(trim($search), '%_\\').'%';

        $query->where(function (Builder $inner) use ($term): void {
            $inner
                ->where('recipient', 'like', $term)
                ->orWhere('event_type', 'like', $term)
                ->orWhere('error_message', 'like', $term);
        });
    }
}
