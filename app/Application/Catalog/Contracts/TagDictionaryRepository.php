<?php

namespace App\Application\Catalog\Contracts;

use App\Application\Catalog\DTO\AdminTagDTO;
use App\Application\Catalog\DTO\CreateAdminTagDTO;
use App\Application\Catalog\DTO\UpdateAdminTagDTO;

interface TagDictionaryRepository
{
    /**
     * @return AdminTagDTO[]
     */
    public function listAll(): array;

    public function findById(int $id): ?AdminTagDTO;

    public function create(CreateAdminTagDTO $dto): AdminTagDTO;

    public function update(UpdateAdminTagDTO $dto): AdminTagDTO;

    public function delete(int $id): void;
}
