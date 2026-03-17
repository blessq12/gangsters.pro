<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientProfileUpdated;

final class OnClientProfileUpdated
{
    public function handle(ClientProfileUpdated $event): void
    {
        // TODO: добавить действия при обновлении профиля клиента
    }
}

