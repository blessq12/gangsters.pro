<?php

namespace App\Shared\Auth;

interface ClientAuthContext
{
    /**
     * Возвращает ID текущего авторизованного клиента.
     *
     * @throws \LogicException если клиент не авторизован или тип не клиентский
     */
    public function currentClientId(): int;
}

