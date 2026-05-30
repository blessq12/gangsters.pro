<?php

namespace Tests\Unit\Application\Catalog;

use App\Application\Catalog\Contracts\TagDictionaryRepository;
use App\Application\Catalog\DTO\AdminTagDTO;
use App\Application\Catalog\Query\GetAdminTagDetailQuery;
use App\Application\Common\Exceptions\ApiException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GetAdminTagDetailQueryTest extends TestCase
{
    #[Test]
    public function execute_returns_tag_when_found(): void
    {
        $tag = new AdminTagDTO(1, 'spicy', 'Острое', 'red', true, 0);

        $repository = $this->createMock(TagDictionaryRepository::class);
        $repository->method('findById')->with(1)->willReturn($tag);

        $result = (new GetAdminTagDetailQuery($repository))->execute(1);

        $this->assertSame($tag, $result);
    }

    #[Test]
    public function execute_throws_when_tag_missing(): void
    {
        $repository = $this->createMock(TagDictionaryRepository::class);
        $repository->method('findById')->with(99)->willReturn(null);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Tag not found.');

        (new GetAdminTagDetailQuery($repository))->execute(99);
    }
}
