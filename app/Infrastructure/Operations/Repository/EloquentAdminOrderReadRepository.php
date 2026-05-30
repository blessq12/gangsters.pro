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
        ?string $phone = null,
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

        if (filled($phone)) {
            $this->applyPhoneFilter($query, $phone);
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
    private function applyPhoneFilter(Builder $query, string $phone): void
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where('customer_phone', 'like', '%'.$digits.'%');
    }
}
