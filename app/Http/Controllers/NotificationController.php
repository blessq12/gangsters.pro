<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
   public function index(Request $request)
   {
       $notifications = Notification::where('session_id', $request->session_id)
                                    ->orWhere('is_mass', true)
                                    ->orderBy('created_at', 'desc')
                                    ->take(10)
                                    ->get();
   
       if ($notifications->isEmpty()) {
           return response()->json([
            'new_message' => false,
            'message' => 'No new notifications found.'
           ], 200);
       }

       return response()->json([
        'notifications' => $notifications,
        'new_message' => true,
       ], 200);
   }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return response()->json(['status' => 'Notification marked as read']);
    }

    public function createForAllUsers(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $users = User::all();

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'message' => $validated['message'],
                'is_read' => false,
            ]);
        }

        return response()->json(['status' => 'Notifications sent to all users']);
    }

    public function createNotification(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'session_id' => 'nullable|string',
            'is_mass' => 'nullable|boolean',
        ]);

        if ($validated['is_mass']) {
            // Create mass notification
            $users = User::all();
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'message' => $validated['message'],
                    'is_read' => false,
                    'is_mass' => true,
                ]);
            }
        } elseif ($validated['user_id']) {
            // Create personal notification
            Notification::create([
                'user_id' => $validated['user_id'],
                'message' => $validated['message'],
                'is_read' => false,
            ]);
        } elseif ($validated['session_id']) {
            // Create session-based notification
            Notification::create([
                'session_id' => $validated['session_id'],
                'message' => $validated['message'],
                'is_read' => false,
            ]);
        }

        return response()->json(['status' => 'Notification created']);
    }
}
