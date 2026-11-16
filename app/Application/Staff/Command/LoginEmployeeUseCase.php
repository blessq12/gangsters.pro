<?php

namespace App\Application\Staff\Command;

use App\Application\Staff\Presenter\EmployeePresenter;
use App\Domain\Staff\Entity\Employee;
use App\Domain\Staff\Port\StaffSessionPort;
use App\Domain\Staff\Repository\EmployeeRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginEmployeeUseCase
{
    public function __construct(
        private readonly EmployeeRepository $employees,
        private readonly StaffSessionPort $session,
        private readonly EmployeePresenter $presenter,
    ) {}

    /**
     * @return array{token: string, employee: array{id: int, name: string, email: string, phone: ?string, role: string, is_active: bool}}
     */
    public function execute(string $email, string $password): array
    {
        $email = trim($email);
        $employee = $email === '' ? null : $this->employees->findByEmail($email);

        if (
            ! $employee instanceof Employee
            || ! $employee->isActive()
            || ! Hash::check($password, $employee->passwordHash())
        ) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        $token = $this->session->login($employee->id());

        return [
            'token' => $token,
            'employee' => $this->presenter->present($employee),
        ];
    }
}
