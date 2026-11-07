<?php

namespace App\Application\Content\Command;

use App\Domain\Content\Repository\OperatorRepository;

final class DeleteOperatorUseCase
{
    public function __construct(
        private readonly OperatorRepository $operators,
    ) {}

    public function execute(int $id): void
    {
        $this->operators->delete($id);
    }
}
