<?php

namespace App\Infrastructure\Notifications\Telegram;

final class TelegramTopicResolver
{
    /**
     * @param  array<string, int|string>  $topics
     */
    public function __construct(
        private readonly array $topics = [],
    ) {
    }

    public function resolve(?string $topic): ?int
    {
        if ($topic === null || $topic === '') {
            return null;
        }

        $value = $this->topics[$topic] ?? null;
        if ($value === null) {
            return null;
        }

        return (int) $value;
    }
}

