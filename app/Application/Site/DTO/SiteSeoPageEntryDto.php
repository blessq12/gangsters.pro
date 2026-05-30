<?php

namespace App\Application\Site\DTO;

final readonly class SiteSeoPageEntryDto
{
    public function __construct(
        public string $path,
        public string $title,
        public string $description,
        public string $robots,
    ) {}
}
