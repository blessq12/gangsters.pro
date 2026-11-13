<?php

namespace App\Http\Controllers\Api;

use App\Application\Staff\Command\LoginEmployeeUseCase;
use App\Application\Staff\Command\LogoutEmployeeUseCase;
use App\Application\Staff\Command\RequestEmployeePasswordResetUseCase;
use App\Application\Staff\Query\GetCurrentEmployeeUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class BackofficeAuthController extends Controller
{
    public function login(Request $request, LoginEmployeeUseCase $login): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $login->execute($validated['email'], $validated['password']);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json($result);
    }

    public function logout(Request $request, LogoutEmployeeUseCase $logout): JsonResponse
    {
        $logout->execute();

        return response()->json(['ok' => true]);
    }

    public function me(GetCurrentEmployeeUseCase $current): JsonResponse
    {
        return response()->json([
            'employee' => $current->execute(),
            'auth_required' => (bool) config('backoffice.auth_required'),
        ]);
    }

    public function forgotPassword(
        Request $request,
        RequestEmployeePasswordResetUseCase $reset,
    ): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        return response()->json($reset->execute($validated['email']));
    }
}
