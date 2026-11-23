<?php

namespace App\Domain\Supporting\Staff\Entity;

use App\Domain\Supporting\Staff\Enum\StaffRole;
use DateTimeImmutable;

/** Агрегат сотрудника backoffice: профиль, роль, филиал (опционально), учётные данные. */
final class Employee
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $email,
        private ?string $phone,
        private StaffRole $role,
        private ?int $branchId,
        private string $passwordHash,
        private bool $isActive,
        private readonly DateTimeImmutable $createdAt,
    ) {}

    public static function hire(
        string $name,
        string $email,
        ?string $phone,
        StaffRole $role,
        string $passwordHash,
        ?int $branchId = null,
    ): self {
        $name = trim($name);
        $email = trim($email);
        $phone = self::normalizeOptional($phone);
        $branchId = self::normalizeBranchId($branchId, $role);

        if ($name === '' || $email === '') {
            throw new \InvalidArgumentException('Имя и email обязательны.');
        }

        if ($passwordHash === '') {
            throw new \InvalidArgumentException('Хеш пароля обязателен.');
        }

        return new self(
            id: null,
            name: $name,
            email: $email,
            phone: $phone,
            role: $role,
            branchId: $branchId,
            passwordHash: $passwordHash,
            isActive: true,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function restore(
        int $id,
        string $name,
        string $email,
        ?string $phone,
        StaffRole $role,
        string $passwordHash,
        bool $isActive,
        DateTimeImmutable $createdAt,
        ?int $branchId = null,
    ): self {
        // Reconstitution из БД: не валим legacy kitchen/courier без филиала.
        // Инварианта филиала — только на hire / changeRole / changeBranch.
        return new self(
            id: $id,
            name: $name,
            email: $email,
            phone: $phone,
            role: $role,
            branchId: self::normalizeStoredBranchId($branchId),
            passwordHash: $passwordHash,
            isActive: $isActive,
            createdAt: $createdAt,
        );
    }

    public function assignId(int $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('id уже назначен.');
        }

        if ($id < 1) {
            throw new \InvalidArgumentException('id должен быть положительным.');
        }

        $this->id = $id;
    }

    public function id(): int
    {
        if ($this->id === null) {
            throw new \LogicException('Сотрудник ещё не сохранён.');
        }

        return $this->id;
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function role(): StaffRole
    {
        return $this->role;
    }

    public function branchId(): ?int
    {
        return $this->branchId;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function rename(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Имя обязательно.');
        }

        $this->name = $name;
    }

    public function changeEmail(string $email): void
    {
        $email = trim($email);
        if ($email === '') {
            throw new \InvalidArgumentException('Email обязателен.');
        }

        $this->email = $email;
    }

    public function changePhone(?string $phone): void
    {
        $this->phone = self::normalizeOptional($phone);
    }

    public function changeRole(StaffRole $role): void
    {
        if ($this->role === $role) {
            throw new \InvalidArgumentException('Роль уже установлена.');
        }

        $this->role = $role;
        $this->branchId = self::normalizeBranchId($this->branchId, $role);
    }

    public function changeBranch(?int $branchId): void
    {
        $this->branchId = self::normalizeBranchId($branchId, $this->role);
    }

    public function changePassword(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new \InvalidArgumentException('Хеш пароля обязателен.');
        }

        $this->passwordHash = $passwordHash;
    }

    public function deactivate(): void
    {
        if (! $this->isActive) {
            throw new \InvalidArgumentException('Сотрудник уже деактивирован.');
        }

        $this->isActive = false;
    }

    public function reactivate(): void
    {
        if ($this->isActive) {
            throw new \InvalidArgumentException('Сотрудник уже активен.');
        }

        $this->isActive = true;
    }

    private static function normalizeOptional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function normalizeStoredBranchId(?int $branchId): ?int
    {
        if ($branchId !== null && $branchId < 1) {
            throw new \InvalidArgumentException('branch_id должен быть положительным.');
        }

        return $branchId;
    }

    private static function normalizeBranchId(?int $branchId, StaffRole $role): ?int
    {
        $branchId = self::normalizeStoredBranchId($branchId);

        if ($role === StaffRole::Kitchen || $role === StaffRole::Courier) {
            if ($branchId === null) {
                throw new \InvalidArgumentException('Для ролей kitchen и courier нужен филиал.');
            }

            return $branchId;
        }

        return $branchId;
    }
}
