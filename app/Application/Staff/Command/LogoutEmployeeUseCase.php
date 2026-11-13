<?php

namespace App\Application\Staff\Command;

use App\Domain\Staff\Port\StaffSessionPort;

final class LogoutEmployeeUseCase
{
    public function __construct(
        private readonly StaffSessionPort $session,
    ) {}

    public function execute(): void
    {
        $this->session->logout();
    }
}
