<?php

namespace App\Http\Controllers\Api;

use App\Application\Client\DTO\AddClientAddressDto;
use App\Application\Client\DTO\ChangePasswordWithTokenDto;
use App\Application\Client\DTO\DeleteClientAddressDto;
use App\Application\Client\DTO\LoginClientDto;
use App\Application\Client\DTO\RegisterClientDto;
use App\Application\Client\DTO\RequestPasswordResetDto;
use App\Application\Client\DTO\UpdateClientProfileDto;
use App\Application\Client\useCases\AddClientAddressUseCase;
use App\Application\Client\useCases\ChangePasswordWithTokenUseCase;
use App\Application\Client\useCases\DeleteClientAddressUseCase;
use App\Application\Client\useCases\GetClientProfileUseCase;
use App\Application\Client\useCases\LoginClientUseCase;
use App\Application\Client\useCases\RegisterClientUseCase;
use App\Application\Client\useCases\RequestPasswordResetUseCase;
use App\Application\Client\useCases\UpdateClientProfileUseCase;
use App\Application\Common\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\AddClientAddressRequest;
use App\Http\Requests\Client\ChangePasswordRequest;
use App\Http\Requests\Client\ForgotPasswordRequest;
use App\Http\Requests\Client\LoginClientRequest;
use App\Http\Requests\Client\RegisterClientRequest;
use App\Http\Requests\Client\UpdateClientProfileRequest;
use App\Infrastructure\Client\Model\CLN_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientController extends Controller
{
    public function __construct(
        private readonly RegisterClientUseCase $registerClient,
        private readonly LoginClientUseCase $loginClient,
        private readonly GetClientProfileUseCase $getClientProfile,
        private readonly UpdateClientProfileUseCase $updateClientProfile,
        private readonly AddClientAddressUseCase $addClientAddress,
        private readonly DeleteClientAddressUseCase $deleteClientAddress,
        private readonly RequestPasswordResetUseCase $requestPasswordReset,
        private readonly ChangePasswordWithTokenUseCase $changePasswordWithToken,
    ) {}

    public function register(RegisterClientRequest $request): JsonResponse
    {
        return response()->json(
            $this->registerClient->execute(
                new RegisterClientDto(
                    name: (string) $request->validated('name'),
                    phone: (string) $request->validated('phone'),
                    email: (string) $request->validated('email'),
                    birthDate: $request->validated('birth_date'),
                    password: (string) $request->validated('password'),
                    consentPersonalData: (bool) $request->validated('consent_personal_data'),
                    consentMarketing: (bool) $request->validated('consent_marketing', false),
                ),
            ),
            201,
        );
    }

    public function login(LoginClientRequest $request): JsonResponse
    {
        return response()->json(
            $this->loginClient->execute(
                new LoginClientDto(
                    phone: $request->validated('phone'),
                    email: $request->validated('email'),
                    password: (string) $request->validated('password'),
                ),
            ),
        );
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(
            $this->getClientProfile->execute($this->resolveClientId($request)),
        );
    }

    public function updateProfile(UpdateClientProfileRequest $request): JsonResponse
    {
        $client = $this->resolveAuthenticatedClient($request);

        return response()->json(
            $this->updateClientProfile->execute(
                new UpdateClientProfileDto(
                    clientId: (int) $client->id,
                    name: (string) $request->validated('name', $client->name),
                    phone: (string) $request->validated('phone', $client->phone),
                    email: $request->has('email')
                        ? $request->validated('email')
                        : $client->email,
                    birthDate: $request->has('birth_date')
                        ? $request->validated('birth_date')
                        : ($client->birth_date?->format('Y-m-d')),
                    consentPersonalData: $request->has('consent_personal_data')
                        ? (bool) $request->validated('consent_personal_data')
                        : null,
                    consentMarketing: $request->has('consent_marketing')
                        ? (bool) $request->validated('consent_marketing')
                        : null,
                ),
            ),
        );
    }

    public function addAddress(AddClientAddressRequest $request): JsonResponse
    {
        return response()->json(
            $this->addClientAddress->execute(
                new AddClientAddressDto(
                    clientId: $this->resolveClientId($request),
                    type: $request->validated('type'),
                    title: $request->validated('title'),
                    street: (string) $request->validated('street'),
                    house: (string) $request->validated('house'),
                    entrance: $request->validated('entrance'),
                    apartment: $request->validated('apartment'),
                    comment: $request->validated('comment'),
                    makeDefault: (bool) $request->validated('make_default', false),
                ),
            ),
            201,
        );
    }

    public function deleteAddress(Request $request, int $addressId): JsonResponse
    {
        return response()->json(
            $this->deleteClientAddress->execute(
                new DeleteClientAddressDto(
                    clientId: $this->resolveClientId($request),
                    addressId: $addressId,
                ),
            ),
        );
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        return response()->json(
            $this->requestPasswordReset->execute(
                new RequestPasswordResetDto(
                    email: (string) $request->validated('email'),
                ),
            ),
        );
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        return response()->json(
            $this->changePasswordWithToken->execute(
                new ChangePasswordWithTokenDto(
                    token: (string) $request->validated('token'),
                    password: (string) $request->validated('password'),
                ),
            ),
        );
    }

    private function resolveAuthenticatedClient(Request $request): CLN_Client
    {
        $client = $request->user('sanctum');

        if (! $client instanceof CLN_Client) {
            throw new UnauthorizedException();
        }

        return $client;
    }

    private function resolveClientId(Request $request): int
    {
        return (int) $this->resolveAuthenticatedClient($request)->id;
    }
}
