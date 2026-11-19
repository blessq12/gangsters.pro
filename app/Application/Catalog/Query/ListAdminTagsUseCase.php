<?php

namespace App\Application\Catalog\Query;

use App\Application\Catalog\Presenter\CatalogPresenter;
use App\Application\Shared\PageParams;
use App\Domain\Supporting\Catalog\Repository\TagRepository;

final class ListAdminTagsUseCase
{
    public function __construct(
        private readonly TagRepository $tags,
        private readonly CatalogPresenter $presenter,
    ) {}

    /**
     * @return array{items: list<array<string, mixed>>, total: int, offset: int, limit: int}
     */
    public function execute(int $offset = 0, int $limit = PageParams::DEFAULT_LIMIT, ?string $q = null): array
    {
        ['offset' => $offset, 'limit' => $limit] = PageParams::normalize($offset, $limit);
        $query = trim((string) ($q ?? ''));
        $needle = $query !== '' ? $query : null;

        $items = [];
        foreach ($this->tags->listPage($offset, $limit, $needle) as $tag) {
            $items[] = $this->presenter->tag($tag);
        }

        return PageParams::wrap(
            $items,
            $this->tags->countAll($needle),
            $offset,
            $limit,
        );
    }
}