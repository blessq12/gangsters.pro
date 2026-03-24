<?php

namespace App\Shared\Auth;

interface ClientAuthContext
{
    /**
     * Возвращает ID текущего авторизованного клиента.
     *
     * @throws \App\Application\Common\Exceptions\UnauthorizedException если клиент не авторизован или тип не клиентский
     */
    public function currentClientId(): int;
}

