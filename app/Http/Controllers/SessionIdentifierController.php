<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SessionIdentifier;
use Illuminate\Support\Str;

class SessionIdentifierController extends Controller
{
    public function create()
    {
        $user = auth('sanctum')->user();
        $userId = $user ? $user->id : null;

        if ($userId) {
            $existingSession = SessionIdentifier::where('user_id', $userId)->first();
            if ($existingSession) {
                return response()->json(['session_id' => $existingSession->session_id]);
            }
        }

        $sessionId = Str::uuid()->toString();
        $sessionIdentifier = SessionIdentifier::create([
            'session_id' => $sessionId,
            'user_id' => $userId
        ]);

        return response()->json(['session_id' => $sessionIdentifier->session_id]);
    }

    public function createPublicSession()
    {
        $sessionId = Str::uuid()->toString();
        $sessionIdentifier = SessionIdentifier::create([
            'session_id' => $sessionId,
            'is_temporary' => true,
        ]);

        return response()->json(['session_id' => $sessionIdentifier->session_id]);
    }

    public function updateUserId(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:session_identifiers,session_id',
        ]);

        $sessionIdentifier = SessionIdentifier::where('session_id', $request->session_id)->first();

        if ($sessionIdentifier && !$sessionIdentifier->user_id && auth('sanctum')->check()) {
            $sessionIdentifier->user_id = auth('sanctum')->id();
            $sessionIdentifier->save();
        }

        return response()->json(['success' => true]);
    }

    public function assignUserId(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:session_identifiers,session_id',
        ]);

        $sessionIdentifier = SessionIdentifier::where('session_id', $request->session_id)->first();

        if ($sessionIdentifier && auth('sanctum')->check()) {
            $sessionIdentifier->user_id = auth('sanctum')->id();
            $sessionIdentifier->is_temporary = false;
            $sessionIdentifier->save();
        }

        return response()->json(['success' => true]);
    }
}
