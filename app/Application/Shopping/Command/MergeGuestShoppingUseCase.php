<?php

namespace App\Application\Shopping\Command;

use App\Application\Shopping\Presenter\ShoppingStatePresenter;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Domain\Shopping\Repositories\ShoppingSessionRepositoryInterface;
use Illuminate\Support\Facades\Cookie as CookieFacade;

/**
 * Привязывает гостевую сессию из cookie к client_id или сливает в существующую клиентскую.
 */
final class MergeGuestShoppingUseCase
{
    public function __construct(
        private readonly ShoppingSessionRepositoryInterface $sessions,
        private readonly ShoppingStatePresenter $presenter,
    ) {
    }

    /**
     * @return array{state: array<string, mixed>, cookie_public_id: string}
     */
    public function execute(ShoppingSession $cookieSession, int $clientId): array
    {
        $ttlDays = max(1, (int) config('shopping.session_ttl_days', 90));
        $expiresAt = (new \DateTimeImmutable)->modify("+{$ttlDays} days");
        $cookieName = (string) config('shopping.session_cookie', 'gangsters_shopping_session');

        $target = $this->sessions->findLatestByClientId($clientId);

        if ($target === null) {
            $cookieSession->setClientId($clientId);
            $cookieSession->setExpiresAt($expiresAt);
            $this->sessions->save($cookieSession);

            CookieFacade::queue(CookieFacade::make(
                name: $cookieName,
                value: $cookieSession->getPublicId(),
                minutes: $ttlDays * 24 * 60,
                path: '/',
                domain: null,
                secure: (bool) config('session.secure', false),
                httpOnly: true,
                raw: false,
                sameSite: 'lax',
            ));

            return [
                'state' => $this->presenter->present($cookieSession),
                'cookie_public_id' => $cookieSession->getPublicId(),
            ];
        }

        if ($target->getId() === $cookieSession->getId()) {
            $cookieSession->setClientId($clientId);
            $cookieSession->setExpiresAt($expiresAt);
            $this->sessions->save($cookieSession);

            CookieFacade::queue(CookieFacade::make(
                name: $cookieName,
                value: $cookieSession->getPublicId(),
                minutes: $ttlDays * 24 * 60,
                path: '/',
                domain: null,
                secure: (bool) config('session.secure', false),
                httpOnly: true,
                raw: false,
                sameSite: 'lax',
            ));

            return [
                'state' => $this->presenter->present($cookieSession),
                'cookie_public_id' => $cookieSession->getPublicId(),
            ];
        }

        $this->mergeLines($target, $cookieSession);
        $this->mergeFavorites($target, $cookieSession);
        $this->mergeDraft($target, $cookieSession);

        $target->setExpiresAt($expiresAt);
        $this->sessions->save($target);

        $guestId = $cookieSession->getId();
        if ($guestId !== $target->getId()) {
            $this->sessions->delete($guestId);
        }

        $reloaded = $this->sessions->getById($target->getId());

        CookieFacade::queue(CookieFacade::make(
            name: $cookieName,
            value: $reloaded->getPublicId(),
            minutes: $ttlDays * 24 * 60,
            path: '/',
            domain: null,
            secure: (bool) config('session.secure', false),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        ));

        return [
            'state' => $this->presenter->present($reloaded),
            'cookie_public_id' => $reloaded->getPublicId(),
        ];
    }

    private function mergeLines(ShoppingSession $target, ShoppingSession $guest): void
    {
        foreach ($guest->getCartLines() as $guestLine) {
            $existingQty = 0;
            foreach ($target->getCartLines() as $tLine) {
                if ($tLine->productId === $guestLine->productId) {
                    $existingQty = $tLine->quantity;
                    break;
                }
            }
            $target->upsertCartLine(
                $guestLine->productId,
                $existingQty + $guestLine->quantity,
                $guestLine->payload,
            );
        }
    }

    private function mergeFavorites(ShoppingSession $target, ShoppingSession $guest): void
    {
        foreach ($guest->getFavoriteProductIds() as $pid) {
            $target->addFavorite($pid);
        }
    }

    private function mergeDraft(ShoppingSession $target, ShoppingSession $guest): void
    {
        $g = $guest->getCheckoutDraft();
        if ($g === null) {
            return;
        }
        $t = $target->getCheckoutDraft();
        if ($t === null || $this->isDraftEmpty($t)) {
            $target->setCheckoutDraft($g);
        }
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function isDraftEmpty(array $draft): bool
    {
        return $draft === [];
    }
}
