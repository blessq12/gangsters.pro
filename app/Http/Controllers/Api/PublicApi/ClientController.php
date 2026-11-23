<?php

namespace App\Http\Controllers\Api\PublicApi;

use App\Application\Client\Command\AddClientAddressUseCase;
use App\Application\Client\Command\DeleteClientAddressUseCase;
use App\Application\Client\Command\LoginClientUseCase;
use App\Application\Client\Command\LogoutClientUseCase;
use App\Application\Client\Command\MergeGuestFavoritesUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\Command\RemoveClientFavoriteUseCase;
use App\Application\Client\Command\RequestPasswordResetUseCase;
use App\Application\Client\Command\ToggleClientFavoriteUseCase;
use App\Application\Client\Command\UpdateClientProfileUseCase;
use App\Application\Client\DTO\AddClientAddressDto;
use App\Application\Client\DTO\LoginClientDto;
use App\Application\Client\DTO\RegisterClientDto;
use App\Application\Client\DTO\ToggleFavoriteDto;
use App\Application\Client\DTO\UpdateClientProfileDto;
use App\Application\Client\Query\GetClientFavoritesUseCase;
use App\Application\Client\Query\GetClientOrderHistoryUseCase;
use App\Application\Client\Query\GetClientProfileUseCase;
use App\Application\Client\Query\GetRepeatableOrderLinesUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Client\Model\ClientModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ClientController extends Controller
{
    public function register(Request $request, RegisterClientUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string'],
            'consent_personal_data' => ['required', 'boolean'],
            'consent_marketing' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute(new RegisterClientDto(
                name: $validated['name'],
                phone: $validated['phone'],
                email: $validated['email'] ?? null,
                birthDate: $validated['birth_date'] ?? null,
                password: $validated['password'],
                consentPersonalData: (bool) $validated['consent_personal_data'],
                consentMarketing: (bool) ($validated['consent_marketing'] ?? false),
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function login(Request $request, LoginClientUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        try {
            $result = $useCase->execute(new LoginClientDto(
                phone: $validated['phone'] ?? null,
                email: $validated['email'] ?? null,
                password: $validated['password'],
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function forgotPassword(Request $request, RequestPasswordResetUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $useCase->execute(
                phone: $validated['phone'] ?? null,
                email: $validated['email'] ?? null,
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function logout(Request $request, LogoutClientUseCase $useCase): JsonResponse
    {
        $client = $request->user();
        $model = $client instanceof ClientModel ? $client : null;

        return response()->json($useCase->execute($model));
    }

    public function profile(Request $request, GetClientProfileUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($this->clientId($request)));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function updateProfile(Request $request, UpdateClientProfileUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'email' => ['sometimes', 'nullable', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'string', 'max:32'],
            'consent_marketing' => ['sometimes', 'nullable', 'boolean'],
        ]);

        try {
            $result = $useCase->execute($this->clientId($request), new UpdateClientProfileDto(
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

        return response()->json($result);
    }

    public function addAddress(Request $request, AddClientAddressUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:64'],
            'title' => ['nullable', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house' => ['required', 'string', 'max:64'],
            'entrance' => ['nullable', 'string', 'max:64'],
            'floor' => ['nullable', 'string', 'max:64'],
            'apartment' => ['nullable', 'string', 'max:64'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'make_default' => ['sometimes', 'boolean'],
        ]);

        try {
            $result = $useCase->execute($this->clientId($request), new AddClientAddressDto(
                type: $validated['type'] ?? null,
                title: $validated['title'] ?? null,
                street: $validated['street'],
                house: $validated['house'],
                entrance: $validated['entrance'] ?? null,
                floor: $validated['floor'] ?? null,
                apartment: $validated['apartment'] ?? null,
                comment: $validated['comment'] ?? null,
                makeDefault: (bool) ($validated['make_default'] ?? false),
                lat: isset($validated['lat']) ? (float) $validated['lat'] : null,
                lng: isset($validated['lng']) ? (float) $validated['lng'] : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, 201);
    }

    public function deleteAddress(
        Request $request,
        string $id,
        DeleteClientAddressUseCase $useCase,
    ): JsonResponse {
        try {
            $result = $useCase->execute($this->clientId($request), $id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function favorites(Request $request, GetClientFavoritesUseCase $useCase): JsonResponse
    {
        try {
            return response()->json($useCase->execute($this->clientId($request)));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }
    }

    public function toggleFavorite(
        Request $request,
        int $productId,
        ToggleClientFavoriteUseCase $useCase,
    ): JsonResponse {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'integer', 'min:0'],
            'price_kopecks' => ['nullable', 'integer', 'min:0'],
        ]);

        $price = $validated['price_kopecks'] ?? $validated['price'] ?? null;

        try {
            $result = $useCase->execute($this->clientId($request), new ToggleFavoriteDto(
                productId: $productId,
                name: $validated['name'] ?? null,
                priceKopecks: $price !== null ? (int) $price : null,
            ));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result, $result['added'] ? 201 : 200);
    }

    public function removeFavorite(
        Request $request,
        int $productId,
        RemoveClientFavoriteUseCase $useCase,
    ): JsonResponse {
        try {
            $result = $useCase->execute($this->clientId($request), $productId);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function mergeFavorites(Request $request, MergeGuestFavoritesUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'favorites' => ['required', 'array'],
            'favorites.*' => ['nullable'],
        ]);

        try {
            $result = $useCase->execute(
                $this->clientId($request),
                $validated['favorites'],
            );
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json($result);
    }

    public function orders(Request $request, GetClientOrderHistoryUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->execute($this->clientId($request)));
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
        if (! $user instanceof ClientModel) {
            throw new InvalidArgumentException('Клиент не авторизован.');
        }

        return (int) $user->id;
    }
}
