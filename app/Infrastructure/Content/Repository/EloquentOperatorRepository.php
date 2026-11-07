<?php

namespace App\Infrastructure\Content\Repository;

use App\Domain\Content\Entity\Operator;
use App\Domain\Content\Repository\OperatorRepository;
use App\Models\User;

final class EloquentOperatorRepository implements OperatorRepository
{
    public function list(): array
    {
        return User::query()
            ->orderBy('id')
            ->get()
            ->map(fn (User $user): Operator => new Operator(
                (int) $user->id,
                (string) $user->name,
                (string) $user->email,
            ))
            ->all();
    }

    public function save(?int $id, string $name, string $email, ?string $password): Operator
    {
        $emailTaken = User::query()
            ->where('email', $email)
            ->when($id !== null, fn ($query) => $query->where('id', '!=', $id))
            ->exists();
        if ($emailTaken) {
            throw new \InvalidArgumentException('Такой email уже занят.');
        }

        $user = $id === null ? new User() : User::query()->find($id);
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Оператор не найден.');
        }

        if ($id === null && ($password === null || $password === '')) {
            throw new \InvalidArgumentException('Нужен пароль.');
        }

        $user->name = $name;
        $user->email = $email;
        if ($password !== null && $password !== '') {
            $user->password = $password;
        }
        $user->save();

        return new Operator((int) $user->id, (string) $user->name, (string) $user->email);
    }

    public function delete(int $id): void
    {
        $user = User::query()->find($id);
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Оператор не найден.');
        }

        $user->delete();
    }
}
