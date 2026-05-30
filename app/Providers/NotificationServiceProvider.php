<?php

namespace App\Providers;

use App\Application\Notifications\Contracts\NotificationDeliveryReadRepository;
use App\Application\Notifications\Ports\NotificationDeliveryLogger;
use App\Infrastructure\Notifications\Repository\EloquentNotificationDeliveryLogger;
use App\Infrastructure\Notifications\Repository\EloquentNotificationDeliveryReadRepository;
use App\Infrastructure\Notifications\Telegram\TelegramChannel;
use App\Infrastructure\Notifications\Telegram\TelegramClient;
use App\Infrastructure\Notifications\Telegram\TelegramTopicResolver;
use App\Shared\Notifications\ThreadedMessageChannel;
use Illuminate\Support\ServiceProvider;

final class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationDeliveryLogger::class, EloquentNotificationDeliveryLogger::class);
        $this->app->bind(NotificationDeliveryReadRepository::class, EloquentNotificationDeliveryReadRepository::class);

        $this->app->singleton(TelegramClient::class, function () {
            return new TelegramClient(
                token: (string) config('services.telegram.token'),
            );
        });

        $this->app->singleton(TelegramTopicResolver::class, function () {
            return new TelegramTopicResolver(
                topics: (array) config('services.telegram.topics', []),
            );
        });

        $this->app->singleton(ThreadedMessageChannel::class, function ($app) {
            return new TelegramChannel(
                client: $app->make(TelegramClient::class),
                topics: $app->make(TelegramTopicResolver::class),
                chatId: (string) config('services.telegram.chat_id'),
                parseMode: (string) config('services.telegram.parse_mode', 'HTML'),
            );
        });
    }
}
