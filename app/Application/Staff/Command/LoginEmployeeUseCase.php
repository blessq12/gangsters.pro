<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\DTO\LoginEmployeeDto;
use App\Application\Staff\Presenter\EmployeePresenter;
use App\Application\Staff\Support\EmployeeAccessToken;
use App\Domain\Supporting\Staff\Entity\Employee;
use App\Domain\Supporting\Staff\Repository\EmployeeRepository;

final class LoginEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{token: string, employee: array<string, mixed>}
     */
    public function execute(LoginEmployeeDto $dto): array
    {
        $email = trim($dto->email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный email.');
        }

        $employee = $this->employees->findByEmail($email);
        if (! $employee instanceof Employee) {
            throw new \InvalidArgumentException('Неверный email или пароль.');
        }

        if (! password_verify($dto->password, $employee->passwordHash())) {
            throw new \InvalidArgumentException('Неверный email или пароль.');
        }

        if (! $employee->isActive()) {
            throw new \InvalidArgumentException('Учётная запись деактивирована.');
        }

        return [
            'token' => EmployeeAccessToken::issue($employee->id()),
            'employee' => $this->presenter->present($employee),
        ];
    }
}
