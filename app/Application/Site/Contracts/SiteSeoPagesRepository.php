<?php

namespace App\Application\Site\Contracts;

interface SiteSeoPagesRepository
{
    /**
     * @return array<string, array{title: string, description: string, robots: string}>
     */
    public function all(): array;

    /**
     * @param  array<string, array{title: string, description: string, robots: string}>  $pages
     */
    public function save(array $pages): void;
}
