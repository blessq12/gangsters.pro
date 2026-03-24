<?php

namespace App\Infrastructure\Notifications\Telegram;

use App\Shared\Notifications\ThreadedMessageChannel;

final class TelegramChannel implements ThreadedMessageChannel
{
    public function __construct(
        private readonly TelegramClient $client,
        private readonly TelegramTopicResolver $topics,
        private readonly string $chatId,
        private readonly string $parseMode = 'HTML',
    ) {
    }

    public function sendToTopic(string|array $message, ?string $topic = null): string
    {
        $payload = [
            'chat_id' => $this->chatId,
            'text' => $this->normalizeMessage($message),
            'parse_mode' => $this->parseMode,
        ];

        $threadId = $this->topics->resolve($topic);
        if ($threadId !== null) {
            $payload['message_thread_id'] = $threadId;
        }

        return $this->client->sendMessage($payload);
    }

    private function normalizeMessage(string|array $message): string
    {
        if (is_array($message)) {
            return implode("\n", $message);
        }

        return $message;
    }
}

