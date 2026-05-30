<?php

namespace App\Infrastructure\Operations\Repository;

use App\Application\Operations\Client\Contracts\AdminClientReadRepository;
use App\Domain\Client\Entity\Client;
use App\Domain\Client\Repository\ClientRepository;
use App\Infrastructure\Client\Model\UR_Client;
use Illuminate\Database\Eloquent\Builder;

final class EloquentAdminClientReadRepository implements AdminClientReadRepository
{
    public function __construct(
        private readonly ClientRepository $clients,
    ) {
    }

    public function paginate(
        ?string $search = null,
        int $page = 1,
        int $perPage = 25,
    ): array {
        $query = UR_Client::query()->withTrashed()->orderByDesc('id');

        if (filled($search)) {
            $this->applySearch($query, $search);
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = [];
        foreach ($paginator->items() as $model) {
            $client = $this->clients->findById((int) $model->id);
            if ($client !== null) {
                $items[] = $client;
            }
        }

        return [
            'items' => $items,
            'total' => $paginator->total(),
        ];
    }

    public function findById(int $id): ?Client
    {
        return $this->clients->findById($id);
    }

    /**
     * @param  Builder<UR_Client>  $query
     */
    private function applySearch(Builder $query, string $search): void
    {
        $term = trim($search);
        if ($term === '') {
            return;
        }

        if (ctype_digit($term)) {
            $query->where(function (Builder $inner) use ($term): void {
                $inner->where('id', (int) $term)
                    ->orWhere('phone', 'like', '%'.$term.'%');
            });

            return;
        }

        $query->where(function (Builder $inner) use ($term): void {
            $inner->where('email', 'like', '%'.$term.'%')
                ->orWhere('name', 'like', '%'.$term.'%')
                ->orWhere('phone', 'like', '%'.$term.'%');
        });
    }
}
