<?php

namespace App\Domain\Content\Repository;

use App\Domain\Content\Entity\Operator;

interface OperatorRepository
{
    /**
     * @return list<Operator>
     */
    public function list(): array;

    public function save(?int $id, string $name, string $email, ?string $password): Operator;

    public function delete(int $id): void;
}
