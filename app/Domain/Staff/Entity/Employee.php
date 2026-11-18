<?php

namespace App\Domain\Staff\Entity;

use App\Domain\Staff\Enum\StaffRole;
use DateTimeImmutable;

/** Агрегат сотрудника backoffice: профиль, роль, учётные данные, активность. */
final class Employee
{
    private function __construct(
        private ?int $id,
        private string $name,
        private string $email,
        private ?string $phone,
        private StaffRole $role,
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
    ): self {
        $name = trim($name);
        $email = trim($email);
        $phone = self::normalizeOptional($phone);

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
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            phone: $phone,
            role: $role,
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
}
