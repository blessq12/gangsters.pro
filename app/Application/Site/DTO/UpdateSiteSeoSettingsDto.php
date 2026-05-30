<?php

namespace App\Application\Site\DTO;

final readonly class UpdateSiteSeoSettingsDto
{
    /**
     * @param  list<SiteSeoPageEntryDto>  $pages
     */
    public function __construct(
        public array $pages,
    ) {}
}
