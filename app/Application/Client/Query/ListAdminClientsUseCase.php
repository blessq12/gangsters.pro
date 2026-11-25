<?php

namespace App\Application\Client\Query;

use App\Application\Client\Presenter\AdminClientPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Client\Entity\Client;
use App\Domain\Supporting\Client\Port\ClientOrdersPort;
use App\Domain\Supporting\Client\Repository\ClientRepository;

final class ListAdminClientsUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
        private readonly ClientOrdersPort $clientOrders,
        private readonly AdminClientPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT, ?string $q = null): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);
        $query = trim((string) ($q ?? ''));

        if ($query === '') {
            $page = $this->clients->listPage($offset, $limit);

            return PageParams::wrap(
                $this->presenter->presentList($page, $this->summariesFor($page)),
                $this->clients->count(),
                $offset,
                $limit,
            );
        }

        $all = $this->clients->listPage(0, max(1, $this->clients->count()));
        $filtered = array_values(array_filter(
            $all,
            fn (Client $client): bool => $this->matches($client, $query),
        ));

        $total = count($filtered);
        $page = array_slice($filtered, $offset, $limit);

        return PageParams::wrap(
            $this->presenter->presentList($page, $this->summariesFor($page)),
            $total,
            $offset,
            $limit,
        );
    }

    /**
     * @param  list<Client>  $clients
     * @return array<int, array{orders_count: int, last_order_at: string|null}>
     */
    private function summariesFor(array $clients): array
    {
        $ids = [];
        foreach ($clients as $client) {
            if ($client->hasId()) {
                $ids[] = $client->id();
            }
        }

        return $this->clientOrders->summariesByClientIds($ids);
    }

    private function matches(Client $client, string $query): bool
    {
        $needle = mb_strtolower($query);
        $haystacks = [
            mb_strtolower($client->name()),
            mb_strtolower($client->phone()),
            mb_strtolower((string) ($client->email() ?? '')),
        ];

        foreach ($haystacks as $haystack) {
            if ($haystack !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        $digitsNeedle = preg_replace('/\D+/', '', $query) ?? '';
        if ($digitsNeedle !== '') {
            $phoneDigits = preg_replace('/\D+/', '', $client->phone()) ?? '';
            if ($phoneDigits !== '' && str_contains($phoneDigits, $digitsNeedle)) {
                return true;
            }
        }

        return false;
    }
}
