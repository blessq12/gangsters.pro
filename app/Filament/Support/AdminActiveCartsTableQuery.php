<?php

namespace App\Filament\Support;

use App\Application\Operations\Shopping\DTO\AdminActiveCartListFilters;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Shopping\Model\SHP_ShoppingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class AdminActiveCartsTableQuery
{
    public function paginate(
        ?string $search,
        int $page,
        int $perPage,
        string $pageName,
    ): LengthAwarePaginator {
        $filters = AdminActiveCartListFilters::fromSearch($search);

        $query = SHP_ShoppingSession::query()
            ->where('expires_at', '>', now())
            ->whereHas('cartLines')
            ->withCount(['cartLines', 'favorites'])
            ->orderByDesc('updated_at');

        $this->applyListFilters($query, $filters);

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $clients = $this->loadClientsForSessions(collect($paginator->items()));

        $items = collect($paginator->items())
            ->map(function (SHP_ShoppingSession $session) use ($clients): array {
                $client = $session->client_id !== null
                    ? $clients->get((int) $session->client_id)
                    : null;

                return [
                    'id' => (int) $session->id,
                    'public_id' => (string) $session->public_id,
                    'client_id' => $session->client_id !== null ? (int) $session->client_id : null,
                    'client_label' => $this->formatClientLabel($session->client_id, $client),
                    'client_badge_color' => $session->client_id !== null ? 'success' : 'gray',
                    'cart_lines_count' => (int) $session->cart_lines_count,
                    'favorites_count' => (int) $session->favorites_count,
                    'updated_at' => $session->updated_at?->toIso8601String(),
                    'expires_at' => $session->expires_at?->toIso8601String(),
                ];
            })
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
     * @param  Collection<int, SHP_ShoppingSession>  $sessions
     * @return Collection<int, UR_Client>
     */
    private function loadClientsForSessions(Collection $sessions): Collection
    {
        $clientIds = $sessions
            ->pluck('client_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($clientIds === []) {
            return collect();
        }

        return UR_Client::query()
            ->whereIn('id', $clientIds)
            ->get()
            ->keyBy('id');
    }

    private function formatClientLabel(?int $clientId, ?UR_Client $client): string
    {
        if ($clientId === null) {
            return 'Гость';
        }

        if ($client === null) {
            return 'Клиент #'.$clientId;
        }

        $name = trim((string) ($client->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($client->email ?? ''));
        if ($email !== '') {
            return $email;
        }

        $phone = trim((string) ($client->phone ?? ''));
        if ($phone !== '') {
            return $phone;
        }

        return 'Клиент #'.$clientId;
    }

    /**
     * @param  Builder<SHP_ShoppingSession>  $query
     */
    private function applyListFilters(Builder $query, AdminActiveCartListFilters $filters): void
    {
        if (! $filters->isActive()) {
            return;
        }

        $query->where(function (Builder $inner) use ($filters): void {
            if ($filters->sessionId !== null) {
                $inner->orWhere('id', $filters->sessionId);
            }

            if ($filters->clientId !== null) {
                $inner->orWhere('client_id', $filters->clientId);
            }

            if (filled($filters->publicId)) {
                $inner->orWhere('public_id', $filters->publicId);
            }

            if (filled($filters->orderId)) {
                $inner->orWhereIn('client_id', function ($subquery) use ($filters): void {
                    $subquery->select('client_id')
                        ->from('ORD_orders')
                        ->where('id', $filters->orderId)
                        ->whereNotNull('client_id');
                });
            }
        });
    }
}
