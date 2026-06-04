<?php

namespace App\Filament\Support;

use App\Infrastructure\Order\Model\ORD_Order;
use App\Support\Money;
use App\Support\Order\OrderStatusLabels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class AdminOrderTableQuery
{
    public function paginate(
        ?string $status,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $search,
        ?string $paymentStatus,
        int $page,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator {
        $query = ORD_Order::query()->orderByDesc('created_at');

        if (filled($status)) {
            $query->where('status', $status);
        }

        if (filled($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if (filled($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if (filled($paymentStatus)) {
            $query->where('payment_status', $paymentStatus);
        }

        if (filled($search)) {
            $this->applySearchFilter($query, $search);
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = collect($paginator->items())
            ->map(fn (ORD_Order $order): array => $this->presentRow($order))
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
     * @param  Builder<ORD_Order>  $query
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $term = trim($search);
        if ($term === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        if (ctype_digit($term)) {
            $query->where('id', $term);

            return;
        }

        $digits = preg_replace('/\D+/', '', $term) ?? '';
        $query->where(function (Builder $inner) use ($term, $digits): void {
            if ($digits !== '') {
                $inner->orWhere('customer_phone', 'like', '%'.$digits.'%');
            }

            $inner->orWhere('customer_name', 'like', '%'.$term.'%');
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function presentRow(ORD_Order $order): array
    {
        $status = (string) $order->status;

        return [
            'id' => (string) $order->id,
            'client_id' => $order->client_id,
            'status' => $status,
            'status_label' => OrderStatusLabels::statusLabel($status),
            'customer_name' => (string) ($order->customer_name ?? ''),
            'customer_phone' => (string) ($order->customer_phone ?? ''),
            'total' => Money::kopecksToApiRubles((int) $order->total),
            'payment_status' => $order->payment_status,
            'created_at' => $order->created_at?->toIso8601String() ?? '',
        ];
    }
}
