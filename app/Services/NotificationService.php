<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\SessionIdentifier;

class NotificationService
{
    /**
     * Sends a notification to a specific session.
     *
     * @param string $sessionId The session identifier.
     * @param string $message The notification message.
     * @param string|null $icon The optional icon for the notification.
     * @param string $type The type of notification (default is 'info').
     */
    public function sendNotification($message, $icon = null, $type = 'info')
    {
        
        Notification::create([
            'message' => $message,
            'icon' => $icon,
            'type' => $type,
            'is_mass' => true
        ]);
        
    }

    /**
     * Sends a personal notification to a specific user.
     *
     * @param string|null $sessionId The optional session identifier.
     * @param string|null $userId The optional user identifier.
     * @param string $message The notification message.
     * @param string|null $icon The optional icon for the notification.
     * @param string $type The type of notification (default is 'info').
     */
    public function sendPersonalNotification($sessionId, $userId = null, $message, $icon = null, $type = 'info')
    {
        if ($sessionId) {
            // Create notification using session ID
            $userId = SessionIdentifier::where('session_id', $sessionId)->first()->user_id ?? null;
            Notification::create([
                'session_id' => $sessionId,
                'user_id' => $userId,
                'message' => $message,
                'icon' => $icon,
                'type' => $type,
            ]);
        } elseif ($userId) {
            // Create notification using user ID
            Notification::create([
                'session_id' => null, // or handle session ID if needed
                'user_id' => $userId,
                'message' => $message,
                'icon' => $icon,
                'type' => $type,
            ]);
        } else {
            throw new \Exception('Either session ID or user ID must be provided');
        }
    }
}
