<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Staff\Command\LoginEmployeeUseCase;
use App\Application\Staff\Command\LogoutEmployeeUseCase;
use App\Application\Staff\Command\RequestEmployeePasswordResetUseCase;
use App\Application\Staff\DTO\LoginEmployeeDto;
use App\Application\Staff\Query\GetCurrentEmployeeUseCase;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class AuthController extends Controller
{
    public function login(Request $request, LoginEmployeeUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $useCase->execute(new LoginEmployeeDto(
                email: $validated['email'],
                password: $validated['password'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function logout(Request $request, LogoutEmployeeUseCase $useCase): JsonResponse
    {
        $user = $request->user('sanctum');

        return response()->json(
            $useCase->execute($user instanceof User ? $user : null),
        );
    }

    public function me(Request $request, GetCurrentEmployeeUseCase $useCase): JsonResponse
    {
        $user = $request->user('sanctum');
        $userId = $user instanceof User ? (int) $user->id : null;

        return response()->json($useCase->execute($userId));
    }

    public function forgotPassword(
        Request $request,
        RequestEmployeePasswordResetUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $result = $useCase->execute($validated['email']);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }
}
