<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\ClientPresenter;
use App\Domain\Crm\Entity\Client;
use App\Domain\Crm\Repository\ClientRepository;
use App\Domain\Crm\Repository\OrderHistoryRepository;

final class ListAdminClientsUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly OrderHistoryRepository $history,
        private readonly ClientPresenter $presenter,
    ) {}

    /**
     * @return array{clients: list<array<string, mixed>>, page: int, per_page: int, total: int}
     */
    public function execute(int $page, int $perPage): array
    {
        $perPage = max(1, min(100, $perPage));
        $total = $this->clients->count();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);

        $rows = [];
        foreach ($this->clients->listPage(($page - 1) * $perPage, $perPage) as $client) {
            $rows[] = $this->present($client);
        }

        return [
            'clients' => $rows,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Client $client): array
    {
        $profile = $this->presenter->present($client);
        $history = [];

        if ($client->hasId()) {
            foreach ($this->history->listByClientId($client->id()) as $entry) {
                $snapshot = $entry->orderSnapshot();
                $orderId = $snapshot['id'] ?? null;
                $total = $snapshot['total'] ?? null;
                $history[] = [
                    'id' => $entry->id(),
                    'placed_at' => $entry->placedAt()->format(DATE_ATOM),
                    'summary' => 'заказ #'.(is_scalar($orderId) ? (string) $orderId : '—')
                        .' · '.(is_numeric($total) ? (string) (int) $total : '0').' ₽',
                ];
            }
        }

        $profile['order_history'] = $history;

        return $profile;
    }
}
