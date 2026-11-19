<?php

namespace App\Application\Crm\Query;

use App\Application\Crm\Presenter\AdminClientPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Customer\Entity\Client;
use App\Domain\Supporting\Customer\Repository\ClientRepository;

final class ListAdminClientsUseCase
{
    public function __construct(
        private readonly ClientRepository $clients,
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
            return PageParams::wrap(
                $this->presenter->presentList(
                    $this->clients->listPage($offset, $limit),
                ),
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
            $this->presenter->presentList($page),
            $total,
            $offset,
            $limit,
        );
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
