<?php

namespace App\Filament\Support;

use App\Infrastructure\Client\Model\UR_Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class AdminClientTableQuery
{
    public function paginate(
        ?string $search,
        int $page,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator {
        $query = UR_Client::query()->withTrashed()->orderByDesc('id');

        if (filled($search)) {
            $this->applySearch($query, $search);
        }

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $items = collect($paginator->items())
            ->map(fn (UR_Client $client): array => [
                'id' => (int) $client->id,
                'name' => $client->name,
                'phone' => (string) ($client->phone ?? ''),
                'email' => $client->email,
                'status' => (string) ($client->status ?? ''),
                'created_at' => $client->created_at?->toIso8601String() ?? '',
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
