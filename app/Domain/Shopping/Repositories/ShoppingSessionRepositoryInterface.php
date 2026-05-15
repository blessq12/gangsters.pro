<?php

namespace App\Domain\Shopping\Repositories;

use App\Domain\Shopping\Entities\ShoppingSession;

interface ShoppingSessionRepositoryInterface
{
    public function getById(int $id): ShoppingSession;

    public function findByPublicId(string $publicId): ?ShoppingSession;

    public function findLatestByClientId(int $clientId): ?ShoppingSession;

    public function save(ShoppingSession $session): void;

    public function touchExpiresAt(int $sessionId, \DateTimeImmutable $expiresAt): void;

    /**
     * Создать новую сессию с заданным public_id и сроком жизни.
     */
    public function create(string $publicId, ?int $clientId, \DateTimeImmutable $expiresAt): ShoppingSession;

    public function delete(int $id): void;
}
