<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\DTO\SaveEmployeeDto;
use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Generic\Staff\Entity\Employee;
use App\Domain\Generic\Staff\Enum\StaffRole;
use App\Domain\Generic\Staff\Repository\EmployeeRepository;
use Illuminate\Support\Facades\Hash;

final class SaveEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{employee: array<string, mixed>, created: bool}
     */
    public function execute(SaveEmployeeDto $dto): array
    {
        $name = trim($dto->name);
        $email = trim($dto->email);
        $phone = $this->nullable($dto->phone);

        if ($name === '') {
            throw new \InvalidArgumentException('Имя обязательно.');
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email.');
        }

        $role = StaffRole::tryFrom(trim($dto->role));
        if (! $role instanceof StaffRole) {
            throw new \InvalidArgumentException('Некорректная роль.');
        }

        $branchId = $dto->branchId;
        if ($branchId !== null && $branchId < 1) {
            throw new \InvalidArgumentException('Некорректный филиал.');
        }

        if (
            ($role === StaffRole::Kitchen || $role === StaffRole::Courier)
            && $branchId === null
        ) {
            throw new \InvalidArgumentException('Для ролей kitchen и courier нужен филиал.');
        }

        if ($dto->id === null) {
            return $this->create($name, $email, $phone, $role, $branchId, $dto);
        }

        return $this->update($dto->id, $name, $email, $phone, $role, $branchId, $dto);
    }

    /**
     * @return array{employee: array<string, mixed>, created: bool}
     */
    private function create(
        string $name,
        string $email,
        ?string $phone,
        StaffRole $role,
        ?int $branchId,
        SaveEmployeeDto $dto,
    ): array {
        $password = (string) ($dto->password ?? '');
        if (strlen($password) < 6) {
            throw new \InvalidArgumentException('Пароль должен быть не короче 6 символов.');
        }

        if ($this->employees->findByEmail($email) !== null) {
            throw new \InvalidArgumentException('Сотрудник с таким email уже существует.');
        }

        $employee = Employee::hire(
            name: $name,
            email: $email,
            phone: $phone,
            role: $role,
            passwordHash: Hash::make($password),
            branchId: $branchId,
        );

        if ($dto->isActive === false) {
            $employee->deactivate();
        }

        $this->employees->save($employee);

        return [
            'employee' => $this->presenter->present($employee),
            'created' => true,
        ];
    }

    /**
     * @return array{employee: array<string, mixed>, created: bool}
     */
    private function update(
        int $id,
        string $name,
        string $email,
        ?string $phone,
        StaffRole $role,
        ?int $branchId,
        SaveEmployeeDto $dto,
    ): array {
        $existing = $this->employees->findById($id);
        if ($existing === null) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        $other = $this->employees->findByEmail($email);
        if ($other !== null && $other->id() !== $id) {
            throw new \InvalidArgumentException('Сотрудник с таким email уже существует.');
        }

        $passwordHash = $existing->passwordHash();
        if ($dto->password !== null && trim($dto->password) !== '') {
            if (strlen($dto->password) < 6) {
                throw new \InvalidArgumentException('Пароль должен быть не короче 6 символов.');
            }
            $passwordHash = Hash::make($dto->password);
        }

        $isActive = $dto->isActive ?? $existing->isActive();

        $employee = Employee::restore(
            id: $id,
            name: $name,
            email: $email,
            phone: $phone,
            role: $role,
            passwordHash: $passwordHash,
            isActive: $isActive,
            createdAt: $existing->createdAt(),
            branchId: $branchId,
        );

        $this->employees->save($employee);

        return [
            'employee' => $this->presenter->present($employee),
            'created' => false,
        ];
    }

    private function nullable(?string $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
