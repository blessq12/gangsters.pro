<?php

namespace App\Infrastructure\Staff\Repository;

use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Enum\StaffRole;
use App\Domain\Staff\Repository\EmployeeRepository;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;

final class EloquentEmployeeRepository implements EmployeeRepository
{
    public function save(Employee $employee): void
    {
        $user = $employee->hasId()
            ? User::query()->find($employee->id())
            : new User();

        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $user->name = $employee->name();
        $user->email = $employee->email();
        $user->tel = $employee->phone();
        $user->role = $employee->role()->value;
        $user->is_active = $employee->isActive();
        $user->password = $employee->passwordHash();
        $user->save();

        if (! $employee->hasId()) {
            $employee->assignId((int) $user->id);
        }
    }

    public function findById(int $id): ?Employee
    {
        $user = User::query()->find($id);

        return $user instanceof User ? $this->map($user) : null;
    }

    public function findByEmail(string $email): ?Employee
    {
        $user = User::query()->where('email', trim($email))->first();

        return $user instanceof User ? $this->map($user) : null;
    }

    public function list(?StaffRole $role = null, ?bool $isActive = null): array
    {
        /** @var Builder<User> $query */
        $query = User::query()->orderBy('id');

        if ($role instanceof StaffRole) {
            $query->where('role', $role->value);
        }
        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->get()
            ->map(fn (User $user): Employee => $this->map($user))
            ->all();
    }

    public function delete(int $id): void
    {
        $user = User::query()->find($id);
        if (! $user instanceof User) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $user->delete();
    }

    private function map(User $user): Employee
    {
        $role = StaffRole::tryFrom((string) $user->role) ?? StaffRole::Administrator;

        return Employee::restore(
            id: (int) $user->id,
            name: (string) $user->name,
            email: (string) $user->email,
            phone: $user->tel !== null && $user->tel !== '' ? (string) $user->tel : null,
            role: $role,
            passwordHash: (string) $user->getAuthPassword(),
            isActive: (bool) $user->is_active,
            createdAt: $user->created_at?->toDateTimeImmutable() ?? new DateTimeImmutable(),
        );
    }
}
