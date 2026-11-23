<?php

namespace App\Application\Shared;

final class PageParams
{
    public const DEFAULT_LIMIT = 20;

    public const MAX_LIMIT = 100;

    /**
     * @return array{offset: int, limit: int}
     */
    public static function normalize(int $offset, int $limit): array
    {
        $normalizedLimit = $limit > 0 ? $limit : self::DEFAULT_LIMIT;

        return [
            'offset' => max(0, $offset),
            'limit' => max(1, min(self::MAX_LIMIT, $normalizedLimit)),
        ];
    }

    /**
     * @param  list<mixed>  $items
     * @return array{items: list<mixed>, total: int, offset: int, limit: int}
     */
    public static function wrap(array $items, int $total, int $offset, int $limit): array
    {
        return [
            'items' => array_values($items),
            'total' => max(0, $total),
            'offset' => $offset,
            'limit' => $limit,
        ];
    }
}
