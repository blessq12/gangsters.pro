<?php

namespace Tests\Unit\Application\Site;

use App\Application\Site\Command\UpdateAdminSiteSeoSettingsUseCase;
use App\Application\Site\DTO\SiteSeoPageEntryDto;
use App\Application\Site\DTO\UpdateSiteSeoSettingsDto;
use App\Application\Site\Query\GetAdminSiteSeoSettingsQuery;
use App\Application\Site\SiteSeoResolver;
use App\Application\Site\Support\SiteSeoSettingsValidator;
use Tests\TestCase;

final class UpdateAdminSiteSeoSettingsUseCaseTest extends TestCase
{
    public function test_execute_persists_normalized_pages(): void
    {
        $repository = new InMemorySiteSeoPagesRepository;
        $resolver = new SiteSeoResolver($repository);
        $useCase = new UpdateAdminSiteSeoSettingsUseCase(
            $repository,
            new SiteSeoSettingsValidator($resolver),
            $resolver,
            new GetAdminSiteSeoSettingsQuery($repository),
        );

        $useCase->execute(new UpdateSiteSeoSettingsDto([
            new SiteSeoPageEntryDto(
                path: 'delivery',
                title: 'Доставка',
                description: 'Описание доставки',
                robots: 'INDEX,FOLLOW',
            ),
        ]));

        $saved = $repository->all();

        $this->assertArrayHasKey('/delivery', $saved);
        $this->assertSame('Доставка', $saved['/delivery']['title']);
        $this->assertSame('index,follow', $saved['/delivery']['robots']);
    }
}
