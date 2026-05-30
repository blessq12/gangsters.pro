<?php

namespace App\Infrastructure\Operations\Repository;

use App\Application\Operations\Order\Contracts\AdminOrderReadRepository;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Order\Model\ORD_Order;
use Illuminate\Database\Eloquent\Builder;

final class EloquentAdminOrderReadRepository implements AdminOrderReadRepository
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
    ) {
    }

    public function paginate(
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $search = null,
        ?string $paymentStatus = null,
        ?int $clientId = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
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

        if (filled($search)) {
            $this->applySearchFilter($query, $search);
        }

        if (filled($paymentStatus)) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = [];
        foreach ($paginator->items() as $model) {
            $items[] = $this->orders->getById((string) $model->id);
        }

        return [
            'items' => $items,
            'total' => $paginator->total(),
        ];
    }

    public function findById(string $id): ?Order
    {
        try {
            return $this->orders->getById($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return null;
        }
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
}
