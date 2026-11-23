<?php

namespace App\Http\Controllers\Api\Backoffice;

use App\Application\Crm\Command\AddClientAddressUseCase;
use App\Application\Crm\Command\DeleteClientAddressUseCase;
use App\Application\Crm\Command\EnsureClientByPhoneUseCase;
use App\Application\Crm\Command\SetDefaultClientAddressUseCase;
use App\Application\Crm\Command\UpdateClientAddressUseCase;
use App\Application\Crm\Command\UpdateClientProfileUseCase;
use App\Application\Crm\DTO\AddClientAddressDto;
use App\Application\Crm\DTO\UpdateClientAddressDto;
use App\Application\Crm\DTO\UpdateClientProfileDto;
use App\Application\Crm\Query\GetAdminClientUseCase;
use App\Application\Crm\Query\ListAdminClientsUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ClientController extends Controller
{
    public function ensure(Request $request, EnsureClientByPhoneUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
        ]);

        try {
            return response()->json($useCase->execute(
                name: (string) $validated['name'],
                phone: (string) $validated['phone'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function index(Request $request, ListAdminClientsUseCase $useCase): JsonResponse
    {
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 20);
        $q = $request->query('q');

        return response()->json($useCase->execute(
            offset: $offset,
            limit: $limit,
            q: is_string($q) ? $q : null,
        ));
    }

    public function show(int $id, GetAdminClientUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($id));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function updateProfile(
        Request $request,
        int $id,
        UpdateClientProfileUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'email' => ['sometimes', 'nullable', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'string', 'max:32'],
            'consent_marketing' => ['sometimes', 'nullable', 'boolean'],
        ]);

        try {
            $result = $useCase->execute($id, new UpdateClientProfileDto(
                name: array_key_exists('name', $validated) ? $validated['name'] : null,
                phone: array_key_exists('phone', $validated) ? $validated['phone'] : null,
                email: array_key_exists('email', $validated) ? $validated['email'] : null,
                birthDate: array_key_exists('birth_date', $validated) ? $validated['birth_date'] : null,
                consentMarketing: array_key_exists('consent_marketing', $validated)
                    ? ($validated['consent_marketing'] !== null ? (bool) $validated['consent_marketing'] : null)
                    : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['client' => $result]);
    }

    public function addAddress(
        Request $request,
        int $id,
        AddClientAddressUseCase $useCase,
    ): JsonResponse {
        $validated = $this->validateAddress($request);

        try {
            $result = $useCase->execute($id, new AddClientAddressDto(
                type: $validated['type'] ?? null,
                title: $validated['title'] ?? null,
                street: $validated['street'],
                house: $validated['house'],
                entrance: $validated['entrance'] ?? null,
                floor: $validated['floor'] ?? null,
                apartment: $validated['apartment'] ?? null,
                comment: $validated['comment'] ?? null,
                makeDefault: (bool) ($validated['make_default'] ?? false),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['address' => $result], 201);
    }

    public function updateAddress(
        Request $request,
        int $id,
        string $addressId,
        UpdateClientAddressUseCase $useCase,
    ): JsonResponse {
        $validated = $this->validateAddress($request, requireStreetHouse: true);

        try {
            $result = $useCase->execute($id, new UpdateClientAddressDto(
                addressId: $addressId,
                type: $validated['type'] ?? null,
                title: $validated['title'] ?? null,
                street: $validated['street'],
                house: $validated['house'],
                entrance: $validated['entrance'] ?? null,
                floor: $validated['floor'] ?? null,
                apartment: $validated['apartment'] ?? null,
                comment: $validated['comment'] ?? null,
                makeDefault: array_key_exists('make_default', $validated)
                    ? (bool) $validated['make_default']
                    : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['address' => $result]);
    }

    public function deleteAddress(
        int $id,
        string $addressId,
        DeleteClientAddressUseCase $useCase,
    ): JsonResponse {
        try {
            $result = $useCase->execute($id, $addressId);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['client' => $result]);
    }

    public function setDefaultAddress(
        int $id,
        string $addressId,
        SetDefaultClientAddressUseCase $useCase,
    ): JsonResponse {
        try {
            $result = $useCase->execute($id, $addressId);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['client' => $result]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAddress(Request $request, bool $requireStreetHouse = true): array
    {
        return $request->validate([
            'type' => ['nullable', 'string', 'max:64'],
            'title' => ['nullable', 'string', 'max:255'],
            'street' => [$requireStreetHouse ? 'required' : 'nullable', 'string', 'max:255'],
            'house' => [$requireStreetHouse ? 'required' : 'nullable', 'string', 'max:64'],
            'entrance' => ['nullable', 'string', 'max:64'],
            'floor' => ['nullable', 'string', 'max:64'],
            'apartment' => ['nullable', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'make_default' => ['sometimes', 'boolean'],
        ]);
    }
}
