<?php

namespace App\Application\Site\Support;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Site\DTO\SiteSeoPageEntryDto;
use App\Application\Site\DTO\UpdateSiteSeoSettingsDto;
use App\Application\Site\SiteSeoResolver;

final class SiteSeoSettingsValidator
{
    private const ROBOTS_PATTERN = '/^(index|noindex),(follow|nofollow)$/i';

    private const MAX_TITLE_LENGTH = 255;

    private const MAX_DESCRIPTION_LENGTH = 500;

    public function __construct(
        private readonly SiteSeoResolver $resolver,
    ) {}

    public function assertValid(UpdateSiteSeoSettingsDto $dto): void
    {
        $seenPaths = [];

        foreach ($dto->pages as $page) {
            $this->assertPageValid($page);

            $normalized = $this->resolver->normalizePath($page->path);
            if (isset($seenPaths[$normalized])) {
                throw new ApiException("Дублируется путь: {$normalized}", 422);
            }

            $seenPaths[$normalized] = true;
        }
    }

    private function assertPageValid(SiteSeoPageEntryDto $page): void
    {
        $path = trim($page->path);
        if ($path === '') {
            throw new ApiException('Укажите путь страницы.', 422);
        }

        if (str_contains($path, '?') || str_contains($path, '#')) {
            throw new ApiException('Путь не должен содержать query или fragment.', 422);
        }

        if (! preg_match('#^/?[a-zA-Z0-9_\-/]*$#', $path)) {
            throw new ApiException('Недопустимые символы в пути страницы.', 422);
        }

        $title = trim($page->title);
        if ($title === '') {
            throw new ApiException('Заголовок страницы обязателен.', 422);
        }

        if (mb_strlen($title) > self::MAX_TITLE_LENGTH) {
            throw new ApiException(
                'Заголовок не длиннее '.self::MAX_TITLE_LENGTH.' символов.',
                422,
            );
        }

        $description = trim($page->description);
        if ($description === '') {
            throw new ApiException('Описание страницы обязательно.', 422);
        }

        if (mb_strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
            throw new ApiException(
                'Описание не длиннее '.self::MAX_DESCRIPTION_LENGTH.' символов.',
                422,
            );
        }

        $robots = trim($page->robots);
        if ($robots === '' || ! preg_match(self::ROBOTS_PATTERN, $robots)) {
            throw new ApiException(
                'Robots: допустимы index|noindex и follow|nofollow через запятую.',
                422,
            );
        }
    }
}
