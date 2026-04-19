<?php

namespace App\Application\Shopping\Command;

use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use Illuminate\Support\Facades\Cookie as CookieFacade;

final class LogoutShoppingSessionUseCase
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
    ) {
    }

    public function execute(ShoppingSession $session): void
    {
        $this->sessions->delete($session->getId());
        $cookieName = (string) config('shopping.session_cookie', 'gangsters_shopping_session');
        CookieFacade::queue(CookieFacade::forget($cookieName, '/', null, (bool) config('session.secure', false)));
    }
}
