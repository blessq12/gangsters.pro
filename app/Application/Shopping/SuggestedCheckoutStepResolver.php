<?php

namespace App\Application\Shopping;

use App\Domain\Shopping\CartRules\CartState;
use App\Domain\Shopping\Entities\ShoppingSession;

final class SuggestedCheckoutStepResolver
{
    public const STEP_CART = 'cart';

    public const STEP_GUEST = 'guest';

    public const STEP_DELIVERY = 'delivery';

    public const STEP_PAYMENT = 'payment';

    public const STEP_CONFIRM = 'confirm';

    public function resolve(ShoppingSession $session, CartState $resolved): string
    {
        if ($resolved->userLines === []) {
            return self::STEP_CART;
        }

        $draft = $session->getCheckoutDraft() ?? [];

        if (! $this->hasCheckoutDraftProgress($draft)) {
            return self::STEP_CART;
        }

        if ($session->getClientId() === null && ! $this->isGuestContactComplete($draft)) {
            return self::STEP_GUEST;
        }

        if (! $this->isDeliveryComplete($draft, $session)) {
            return self::STEP_DELIVERY;
        }

        if (! $this->isPaymentComplete($draft)) {
            return self::STEP_PAYMENT;
        }

        return self::STEP_CONFIRM;
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function hasCheckoutDraftProgress(array $draft): bool
    {
        $di = $draft['delivery_info'] ?? null;
        if (is_array($di) && is_string($di['method'] ?? null) && $di['method'] !== '') {
            return true;
        }

        $pi = $draft['payment_info'] ?? null;
        if (is_array($pi) && is_string($pi['method'] ?? null) && $pi['method'] !== '') {
            return true;
        }

        $gc = $draft['guest_contact'] ?? null;
        if (is_array($gc) && trim((string) ($gc['phone'] ?? '')) !== '') {
            return true;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function isGuestContactComplete(array $draft): bool
    {
        $gc = $draft['guest_contact'] ?? null;
        if (! is_array($gc)) {
            return false;
        }

        return trim((string) ($gc['name'] ?? '')) !== ''
            && trim((string) ($gc['phone'] ?? '')) !== '';
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function isDeliveryComplete(array $draft, ShoppingSession $session): bool
    {
        $di = $draft['delivery_info'] ?? null;
        if (! is_array($di)) {
            return false;
        }

        $method = $di['method'] ?? null;
        if (! is_string($method) || $method === '') {
            return false;
        }

        if ($method !== 'courier') {
            return true;
        }

        if ($session->getClientId() !== null) {
            return true;
        }

        $address = $di['address'] ?? null;
        if (! is_array($address)) {
            return false;
        }

        return trim((string) ($address['street'] ?? '')) !== ''
            && trim((string) ($address['house'] ?? '')) !== '';
    }

    /**
     * @param  array<string, mixed>  $draft
     */
    private function isPaymentComplete(array $draft): bool
    {
        $pi = $draft['payment_info'] ?? null;
        if (! is_array($pi)) {
            return false;
        }

        $method = $pi['method'] ?? null;

        return is_string($method) && $method !== '';
    }
}
