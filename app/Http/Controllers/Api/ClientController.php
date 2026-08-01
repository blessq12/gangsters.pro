<?php

namespace App\Http\Controllers\Api;

use App\Application\Crm\Command\AddClientAddressUseCase;
use App\Application\Crm\Command\DeleteClientAddressUseCase;
use App\Application\Crm\Query\GetClientProfileUseCase;
use App\Application\Crm\Query\GetRepeatableOrderLinesUseCase;
use App\Application\Crm\Query\ListClientOrdersUseCase;
use App\Application\Crm\Command\LoginClientUseCase;
use App\Application\Crm\Command\RegisterClientUseCase;
use App\Application\Crm\Command\UpdateClientProfileUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Crm\Model\CRM_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientController extends Controller
{
    public function register(Request $request, RegisterClientUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'consent_personal_data' => ['required', 'boolean', 'accepted'],
            'consent_marketing' => ['nullable', 'boolean'],
        ]);

        return response()->json($useCase->execute($validated), 201);
    }

    public function login(Request $request, LoginClientUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        return response()->json($useCase->execute($validated));
    }

    public function profile(Request $request, GetClientProfileUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute($this->clientId($request)));
    }

    public function updateProfile(Request $request, UpdateClientProfileUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:32'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'consent_marketing' => ['sometimes', 'boolean'],
        ]);

        return response()->json($useCase->execute($this->clientId($request), $validated));
    }

    public function addAddress(Request $request, AddClientAddressUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:64'],
            'title' => ['nullable', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house' => ['required', 'string', 'max:64'],
            'entrance' => ['nullable', 'string', 'max:64'],
            'apartment' => ['nullable', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'make_default' => ['nullable', 'boolean'],
        ]);

        return response()->json($useCase->execute($this->clientId($request), $validated), 201);
    }

    public function deleteAddress(
        Request $request,
        string $addressId,
        DeleteClientAddressUseCase $useCase,
    ): JsonResponse {
        return response()->json($useCase->execute($this->clientId($request), $addressId));
    }

    public function orders(Request $request, ListClientOrdersUseCase $useCase): JsonResponse
    {
        return response()->json([
            'data' => $useCase->execute($this->clientId($request)),
        ]);
    }

    public function repeatableLines(
        Request $request,
        int $orderId,
        GetRepeatableOrderLinesUseCase $useCase,
    ): JsonResponse {
        return response()->json($useCase->execute($this->clientId($request), $orderId));
    }

    private function clientId(Request $request): int
    {
        $user = $request->user();
        if (! $user instanceof CRM_Client) {
            abort(401);
        }

        return (int) $user->getKey();
    }
}
