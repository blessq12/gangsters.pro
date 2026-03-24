<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientUnauthorizedAccessDetected;
use App\Shared\Notifications\ThreadedMessageChannel;
use Illuminate\Support\Facades\Cache;

final class OnClientUnauthorizedAccessDetected
{
    public function __construct(
        private readonly ThreadedMessageChannel $notifications,
    ) {
    }

    public function handle(ClientUnauthorizedAccessDetected $event): void
    {
        $key = sha1($event->ip().'|'.$event->path().'|'.$event->method());
        $cacheKey = 'tg:client_unauthorized:'.$key;

        // Антиспам: максимум 1 сообщение в минуту по одинаковому вектору.
        if (!Cache::add($cacheKey, 1, 60)) {
            return;
        }

        $ua = $event->userAgent() ?? 'n/a';
        if (mb_strlen($ua) > 140) {
            $ua = mb_substr($ua, 0, 140).'...';
        }

        $this->notifications->sendToTopic([
            '⛔ <b>Неавторизованный доступ клиента</b>',
            'Method: '.$event->method(),
            'Path: '.$event->path(),
            'IP: '.$event->ip(),
            'UA: '.$ua,
            'At: '.$event->occurredAt()->format(DATE_ATOM),
        ], 'error');
    }
}

