<?php

namespace Tests\Unit\Application\Site;

use App\Application\Common\Exceptions\ApiException;
use App\Application\Site\DTO\SiteSeoPageEntryDto;
use App\Application\Site\DTO\UpdateSiteSeoSettingsDto;
use App\Application\Site\SiteSeoResolver;
use App\Application\Site\Support\SiteSeoSettingsValidator;
use PHPUnit\Framework\TestCase;

final class SiteSeoSettingsValidatorTest extends TestCase
{
    public function test_rejects_invalid_robots(): void
    {
        $validator = new SiteSeoSettingsValidator(new SiteSeoResolver(new InMemorySiteSeoPagesRepository));

        $this->expectException(ApiException::class);

        $validator->assertValid(new UpdateSiteSeoSettingsDto([
            new SiteSeoPageEntryDto(
                path: '/foo',
                title: 'Title',
                description: 'Description',
                robots: 'nofollow',
            ),
        ]));
    }

    public function test_rejects_duplicate_paths_after_normalize(): void
    {
        $validator = new SiteSeoSettingsValidator(new SiteSeoResolver(new InMemorySiteSeoPagesRepository));

        $this->expectException(ApiException::class);

        $validator->assertValid(new UpdateSiteSeoSettingsDto([
            new SiteSeoPageEntryDto('/', 'A', 'Desc A', 'index,follow'),
            new SiteSeoPageEntryDto('', 'B', 'Desc B', 'index,follow'),
        ]));
    }
}
