<?php

namespace App\Application\Company\Contracts;

interface AdminUserRepository
{
    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function list(?string $search, int $page, int $perPage): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(int $id, array $data): array;

    public function delete(int $id): void;
}
