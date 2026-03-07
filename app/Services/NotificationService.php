<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Sends a mass notification (no specific user).
     */
    public function sendNotification(string $message, ?string $icon = null, string $type = 'info'): void
    {
        Notification::create([
            'message' => $message,
            'icon' => $icon,
            'type' => $type,
            'is_mass' => true,
        ]);
    }

    /**
     * Sends a personal notification to a user.
     */
    public function sendPersonalNotification(?int $userId, string $message, ?string $icon = null, string $type = 'info'): void
    {
        if ($userId === null) {
            throw new \InvalidArgumentException('User ID is required for personal notification');
        }
        Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'icon' => $icon,
            'type' => $type,
        ]);
    }
}
