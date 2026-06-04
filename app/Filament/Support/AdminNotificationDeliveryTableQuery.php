<?php

namespace App\Filament\Support;

use App\Infrastructure\Notifications\Model\SYS_NotificationDelivery;
use App\Support\Notifications\NotificationDeliveryLabels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class AdminNotificationDeliveryTableQuery
{
    public function paginate(
        ?string $channel,
        ?string $status,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $search,
        int $page,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator {
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

        $items = collect($paginator->items())
            ->map(fn (SYS_NotificationDelivery $model): array => [
                'id' => (int) $model->id,
                'channel' => (string) $model->channel,
                'channel_label' => NotificationDeliveryLabels::channelLabel($model->channel),
                'event_type' => (string) $model->event_type,
                'event_type_label' => NotificationDeliveryLabels::eventTypeLabel($model->event_type),
                'recipient' => (string) $model->recipient,
                'status' => (string) $model->status,
                'status_label' => NotificationDeliveryLabels::statusLabel($model->status),
                'error_message' => filled($model->error_message) ? (string) $model->error_message : null,
                'payload_json' => filled($model->payload_json) ? (string) $model->payload_json : null,
                'created_at' => $model->created_at?->toIso8601String() ?? '',
            ])
            ->keyBy('id');

        return new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $perPage,
            max(1, $page),
            ['path' => request()->url(), 'pageName' => $pageName],
        );
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
