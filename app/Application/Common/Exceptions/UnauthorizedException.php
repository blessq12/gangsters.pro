<?php

namespace App\Application\Common\Exceptions;

final class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Unauthenticated')
    {
        parent::__construct($message, 401);
    }
}

