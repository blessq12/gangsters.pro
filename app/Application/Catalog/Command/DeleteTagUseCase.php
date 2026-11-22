<?php

namespace App\Application\Catalog\Command;

use App\Domain\Supporting\Catalog\Repository\TagRepository;

final class DeleteTagUseCase
{
    public function __construct(
        private readonly TagRepository $tags,
    ) {}

    /**
     * @return array{ok: true}
     */
    public function execute(int $id): array
    {
        $this->tags->delete($id);

        return ['ok' => true];
    }
}
