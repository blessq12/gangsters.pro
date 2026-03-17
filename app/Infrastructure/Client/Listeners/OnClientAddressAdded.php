<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientAddressAdded;

final class OnClientAddressAdded
{
    public function handle(ClientAddressAdded $event): void
    {
        // TODO: добавить действия при добавлении адреса клиента
    }
}

