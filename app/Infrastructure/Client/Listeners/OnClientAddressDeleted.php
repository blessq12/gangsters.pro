<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientAddressDeleted;

final class OnClientAddressDeleted
{
    public function handle(ClientAddressDeleted $event): void
    {
        // TODO: добавить действия при удалении адреса клиента
    }
}

