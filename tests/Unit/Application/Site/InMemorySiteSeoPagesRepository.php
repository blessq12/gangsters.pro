<?php

namespace Tests\Unit\Application\Site;

use App\Application\Site\Contracts\SiteSeoPagesRepository;

final class InMemorySiteSeoPagesRepository implements SiteSeoPagesRepository
{
    /**
     * @param  array<string, array{title: string, description: string, robots: string}>  $pages
     */
    public function __construct(private array $pages = []) {}

    public function all(): array
    {
        return $this->pages;
    }

    public function save(array $pages): void
    {
        $this->pages = $pages;
    }
}
