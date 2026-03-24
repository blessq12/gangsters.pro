<?php

namespace App\Infrastructure\Client\Listeners;

use App\Domain\Client\Events\ClientLoginFailed;
use App\Shared\Notifications\ThreadedMessageChannel;
use Illuminate\Support\Facades\Cache;

final class OnClientLoginFailed
{
    public function __construct(
        private readonly ThreadedMessageChannel $notifications,
    ) {
    }

    public function handle(ClientLoginFailed $event): void
    {
        $identifierHash = sha1(mb_strtolower(trim($event->identifier())));
        $cacheKey = 'tg:client_login_failed:'.$identifierHash.':'.$event->reason();

        // Антиспам: 1 уведомление на комбинацию identifier+reason в минуту.
        if (!Cache::add($cacheKey, 1, 60)) {
            return;
        }

        $this->notifications->sendToTopic([
            '🚨 <b>Ошибка авторизации клиента</b>',
            'Идентификатор: '.$this->maskIdentifier($event->identifier()),
            'Причина: '.$event->reason(),
            'Время: '.$event->occurredAt()->format(DATE_ATOM),
        ], 'error');
    }

    private function maskIdentifier(string $identifier): string
    {
        $value = trim($identifier);
        if ($value === '') {
            return 'n/a';
        }

        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if ($digits !== '') {
            if (strlen($digits) >= 4) {
                return '***'.substr($digits, -4);
            }

            return '***';
        }

        if (mb_strlen($value) <= 4) {
            return '***';
        }

        return mb_substr($value, 0, 2).'***'.mb_substr($value, -2);
    }
}

