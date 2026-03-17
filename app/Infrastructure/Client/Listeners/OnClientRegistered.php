<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientRegistered;

final class OnClientRegistered
{
    public function handle(ClientRegistered $event): void
    {
        // TODO: добавить действия при регистрации клиента (уведомления, аналитика и т.п.)
    }
}

