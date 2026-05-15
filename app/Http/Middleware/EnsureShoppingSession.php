<?php

namespace App\Http\Middleware;

use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class EnsureShoppingSession
{
    public const ATTRIBUTE_KEY = 'shopping_session';

    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $cookieName = (string) config('shopping.session_cookie', 'gangsters_shopping_session');
        $ttlDays = max(1, (int) config('shopping.session_ttl_days', 90));
        $expiresAt = (new \DateTimeImmutable)->modify("+{$ttlDays} days");

        $user = $request->user('sanctum');
        $clientId = is_object($user) && isset($user->id) ? (int) $user->id : null;

        $cookiePublicId = $request->cookies->get($cookieName);
        $session = null;

        if (is_string($cookiePublicId) && $cookiePublicId !== '') {
            $session = $this->sessions->findByPublicId($cookiePublicId);
            if ($session !== null && $session->getExpiresAt() < new \DateTimeImmutable) {
                $session = null;
            }
        }

        if ($clientId !== null) {
            if ($session !== null) {
                $sidClient = $session->getClientId();
                if ($sidClient !== null && $sidClient !== $clientId) {
                    $session = null;
                }
            }

            if ($session === null) {
                $session = $this->sessions->findLatestByClientId($clientId);
            }

            if ($session === null) {
                $session = $this->sessions->create(Str::uuid()->toString(), $clientId, $expiresAt);
                Cookie::queue($this->makeCookie($cookieName, $session->getPublicId(), $ttlDays));
            } else {
                $session->setExpiresAt($expiresAt);
                $this->sessions->touchExpiresAt($session->getId(), $expiresAt);
                Cookie::queue($this->makeCookie($cookieName, $session->getPublicId(), $ttlDays));
            }
        } else {
            if ($session === null) {
                $session = $this->sessions->create(Str::uuid()->toString(), null, $expiresAt);
                Cookie::queue($this->makeCookie($cookieName, $session->getPublicId(), $ttlDays));
            } else {
                $session->setExpiresAt($expiresAt);
                $this->sessions->touchExpiresAt($session->getId(), $expiresAt);
                Cookie::queue($this->makeCookie($cookieName, $session->getPublicId(), $ttlDays));
            }
        }

        $request->attributes->set(self::ATTRIBUTE_KEY, $session);

        return $next($request);
    }

    private function makeCookie(string $name, string $publicId, int $ttlDays): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            name: $name,
            value: $publicId,
            minutes: $ttlDays * 24 * 60,
            path: '/',
            domain: null,
            secure: (bool) config('session.secure', false),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        );
    }
}
