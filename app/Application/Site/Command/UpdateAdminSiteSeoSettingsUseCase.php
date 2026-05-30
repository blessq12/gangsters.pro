<?php

namespace App\Application\Site\Command;

use App\Application\Site\Contracts\SiteSeoPagesRepository;
use App\Application\Site\DTO\SiteSeoPageEntryDto;
use App\Application\Site\DTO\UpdateSiteSeoSettingsDto;
use App\Application\Site\Query\GetAdminSiteSeoSettingsQuery;
use App\Application\Site\SiteSeoResolver;
use App\Application\Site\Support\SiteSeoSettingsValidator;

final class UpdateAdminSiteSeoSettingsUseCase
{
    public function __construct(
        private readonly SiteSeoPagesRepository $pages,
        private readonly SiteSeoSettingsValidator $validator,
        private readonly SiteSeoResolver $resolver,
        private readonly GetAdminSiteSeoSettingsQuery $settingsQuery,
    ) {}

    /**
     * @return array{
     *     default_title: string,
     *     default_description: string,
     *     pages: list<array{path: string, title: string, description: string, robots: string}>
     * }
     */
    public function execute(UpdateSiteSeoSettingsDto $dto): array
    {
        $this->validator->assertValid($dto);

        $stored = [];

        foreach ($dto->pages as $page) {
            $path = $this->resolver->normalizePath($page->path);

            $stored[$path] = [
                'title' => trim($page->title),
                'description' => trim($page->description),
                'robots' => strtolower(trim($page->robots)),
            ];
        }

        ksort($stored);

        $this->pages->save($stored);
        $this->resolver->invalidateCache();

        return $this->settingsQuery->execute();
    }

    /**
     * @param  list<SiteSeoPageEntryDto>  $pages
     */
    public static function pagesFromFormRows(array $pages): UpdateSiteSeoSettingsDto
    {
        $entries = [];

        foreach ($pages as $row) {
            if (! is_array($row)) {
                continue;
            }

            $entries[] = new SiteSeoPageEntryDto(
                path: (string) ($row['path'] ?? ''),
                title: (string) ($row['title'] ?? ''),
                description: (string) ($row['description'] ?? ''),
                robots: (string) ($row['robots'] ?? 'index,follow'),
            );
        }

        return new UpdateSiteSeoSettingsDto($entries);
    }
}
