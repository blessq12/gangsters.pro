<?php

namespace App\Shared\Notifications;

interface ThreadedMessageChannel
{
    /**
     * @param  string|array<int, string>  $message
     */
    public function sendToTopic(string|array $message, ?string $topic = null): string;
}

