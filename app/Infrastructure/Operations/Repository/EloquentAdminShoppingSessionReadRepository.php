<?php

namespace App\Infrastructure\Operations\Repository;

use App\Application\Operations\Shopping\Contracts\AdminShoppingSessionReadRepository;
use App\Infrastructure\Client\Model\UR_Client;
use App\Infrastructure\Shopping\Model\SHP_ShoppingSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentAdminShoppingSessionReadRepository implements AdminShoppingSessionReadRepository
{
    public function paginateActiveCarts(
        int $page = 1,
        int $perPage = 25,
        ?int $clientId = null,
        ?int $sessionId = null,
        ?string $publicId = null,
        ?string $orderId = null,
    ): array {
        $query = SHP_ShoppingSession::query()
            ->where('expires_at', '>', now())
            ->whereHas('cartLines')
            ->withCount(['cartLines', 'favorites'])
            ->orderByDesc('updated_at');

        $this->applyListFilters($query, $clientId, $sessionId, $publicId, $orderId);

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $clients = $this->loadClientsForSessions(collect($paginator->items()));

        $items = [];
        foreach ($paginator->items() as $session) {
            /** @var SHP_ShoppingSession $session */
            $client = $session->client_id !== null
                ? $clients->get((int) $session->client_id)
                : null;

            $items[] = [
                'id' => (int) $session->id,
                'public_id' => (string) $session->public_id,
                'client_id' => $session->client_id !== null ? (int) $session->client_id : null,
                'client_label' => $this->formatClientLabel($session->client_id, $client),
                'cart_lines_count' => (int) $session->cart_lines_count,
                'favorites_count' => (int) $session->favorites_count,
                'updated_at' => $session->updated_at?->toIso8601String(),
                'expires_at' => $session->expires_at?->toIso8601String(),
            ];
        }

        return [
            'items' => $items,
            'total' => $paginator->total(),
        ];
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
    private function applyListFilters(
        Builder $query,
        ?int $clientId,
        ?int $sessionId,
        ?string $publicId,
        ?string $orderId,
    ): void {
        if ($clientId === null && $sessionId === null && ! filled($publicId) && ! filled($orderId)) {
            return;
        }

        $query->where(function (Builder $inner) use ($clientId, $sessionId, $publicId, $orderId): void {
            if ($sessionId !== null) {
                $inner->orWhere('id', $sessionId);
            }

            if ($clientId !== null) {
                $inner->orWhere('client_id', $clientId);
            }

            if (filled($publicId)) {
                $inner->orWhere('public_id', $publicId);
            }

            if (filled($orderId)) {
                $inner->orWhereIn('client_id', function ($subquery) use ($orderId): void {
                    $subquery->select('client_id')
                        ->from('ORD_orders')
                        ->where('id', $orderId)
                        ->whereNotNull('client_id');
                });
            }
        });
    }
}
