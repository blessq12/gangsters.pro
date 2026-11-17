<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Пустой auth-stub публичного API. Application снесён — бизнес-логики нет.
 */
final class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string'],
        ]);

        return response()->json(['message' => 'Not implemented.'], 501);
    }

    public function logout(): JsonResponse
    {
        return response()->json(['message' => 'Not implemented.'], 501);
    }

    public function me(): JsonResponse
    {
        return response()->json(['message' => 'Not implemented.'], 501);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:32'],
        ]);

        return response()->json(['message' => 'Not implemented.'], 501);
    }
}
