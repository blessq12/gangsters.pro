<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Enum\StaffRole;
use App\Domain\Staff\Repository\EmployeeRepository;
use Illuminate\Support\Facades\Hash;

final class SaveEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{id: int, name: string, email: string, phone: ?string, role: string, is_active: bool}
     */
    public function execute(
        ?int $id,
        string $name,
        string $email,
        ?string $phone,
        StaffRole $role,
        ?string $password,
        bool $isActive = true,
    ): array {
        $name = trim($name);
        $email = trim($email);
        if ($name === '' || $email === '') {
            throw new \InvalidArgumentException('Нужны имя и email.');
        }

        $existing = $this->employees->findByEmail($email);
        if ($existing instanceof Employee && ($id === null || $existing->id() !== $id)) {
            throw new \InvalidArgumentException('Такой email уже занят.');
        }

        if ($id === null) {
            if ($password === null || $password === '') {
                throw new \InvalidArgumentException('Нужен пароль.');
            }
            if (strlen($password) < 8) {
                throw new \InvalidArgumentException('Пароль короче 8 символов.');
            }

            $employee = Employee::hire(
                name: $name,
                email: $email,
                phone: $phone,
                role: $role,
                passwordHash: Hash::make($password),
            );
            if (! $isActive) {
                $employee->deactivate();
            }
            $this->employees->save($employee);

            return $this->presenter->present($employee);
        }

        $employee = $this->employees->findById($id);
        if (! $employee instanceof Employee) {
            throw new \InvalidArgumentException('Сотрудник не найден.');
        }

        if ($password !== null && $password !== '' && strlen($password) < 8) {
            throw new \InvalidArgumentException('Пароль короче 8 символов.');
        }

        $employee->rename($name);
        $employee->changeEmail($email);
        $employee->changePhone($phone);
        if ($employee->role() !== $role) {
            $employee->changeRole($role);
        }
        if ($password !== null && $password !== '') {
            $employee->changePassword(Hash::make($password));
        }
        if ($isActive && ! $employee->isActive()) {
            $employee->reactivate();
        }
        if (! $isActive && $employee->isActive()) {
            $employee->deactivate();
        }

        $this->employees->save($employee);

        return $this->presenter->present($employee);
    }
}
