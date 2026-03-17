<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientPasswordChanged;

final class OnClientPasswordChanged
{
    public function handle(ClientPasswordChanged $event): void
    {
        // TODO: добавить действия при смене пароля клиента
    }
}

