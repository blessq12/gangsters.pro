<?php

namespace App\Application\Content\Query;

use App\Domain\Content\Repository\OperatorRepository;

final class ListOperatorsUseCase
{
    public function __construct(
        private readonly OperatorRepository $operators,
    ) {}

    /**
     * @return list<array{id: int, name: string, email: string}>
     */
    public function execute(): array
    {
        $rows = [];
        foreach ($this->operators->list() as $operator) {
            $rows[] = [
                'id' => $operator->id(),
                'name' => $operator->name(),
                'email' => $operator->email(),
            ];
        }

        return $rows;
    }
}
