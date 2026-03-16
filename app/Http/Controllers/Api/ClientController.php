<?php

namespace App\Http\Controllers\Api;

use App\Application\Client\Command\AddClientAddressUseCase;
use App\Application\Client\Command\ChangePasswordUseCase;
use App\Application\Client\Command\DeleteClientAddressUseCase;
use App\Application\Client\Command\LoginClientUseCase;
use App\Application\Client\Command\RegisterClientUseCase;
use App\Application\Client\Command\RequestPasswordResetUseCase;
use App\Application\Client\Command\UpdateClientUseCase;
use App\Application\Client\DTO\AddClientAddressDTO;
use App\Application\Client\DTO\ChangePasswordDTO;
use App\Application\Client\DTO\DeleteClientAddressDTO;
use App\Application\Client\DTO\LoginDTO;
use App\Application\Client\DTO\RegisterDTO;
use App\Application\Client\DTO\RequestPasswordResetDTO;
use App\Application\Client\DTO\UpdateClientDTO;
use App\Application\Client\Presenter\ClientPresenter;
use App\Application\Client\Query\GetClientDataUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Client\Model\UR_Client;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ClientController extends Controller
{
    public function __construct(
        private readonly RegisterClientUseCase $registerClient,
        private readonly LoginClientUseCase $loginClient,
        private readonly GetClientDataUseCase $getClientData,
        private readonly UpdateClientUseCase $updateClient,
        private readonly AddClientAddressUseCase $addClientAddress,
        private readonly DeleteClientAddressUseCase $deleteClientAddress,
        private readonly RequestPasswordResetUseCase $requestPasswordReset,
        private readonly ChangePasswordUseCase $changePassword,
        private readonly ClientPresenter $presenter,
    )
    {
        $this->middleware('auth:sanctum')->only([
            'profile',
            'updateProfile',
            'addAddress',
            'deleteAddress',
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
            'consent_personal_data' => ['required', 'boolean'],
            'consent_marketing' => ['required', 'boolean'],
        ]);

        $dto = new RegisterDTO(
            name: $data['name'],
            phone: $data['phone'],
            email: $data['email'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            password: $data['password'] ?? null,
            consentPersonalData: (bool) $data['consent_personal_data'],
            consentMarketing: (bool) $data['consent_marketing'],
        );

        $client = $this->registerClient->execute($dto);

        $model = \App\Infrastructure\Client\Model\UR_Client::findOrFail($client->id());
        $token = $model->createToken('client')->plainTextToken;

        return response()->json([
            'client' => $this->presenter->present($client),
            'token' => $token,
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $dto = new LoginDTO(
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'],
        );

        $client = $this->loginClient->execute($dto);

        $model = \App\Infrastructure\Client\Model\UR_Client::findOrFail($client->id());
        $token = $model->createToken('client')->plainTextToken;

        return response()->json([
            'client' => $this->presenter->present($client),
            'token' => $token,
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $dto = new RequestPasswordResetDTO(
            email: $data['email'],
        );

        $token = $this->requestPasswordReset->execute($dto);

        // На бою сюда можно будет повесить отправку письма.
        return response()->json([
            'status' => true,
            'message' => 'Password reset token generated',
            'token' => $token,
        ]);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $dto = new ChangePasswordDTO(
            token: $data['token'],
            password: $data['password'],
        );

        $client = $this->changePassword->execute($dto);

        return response()->json([
            'status' => true,
            'client' => $this->presenter->present($client),
        ]);
    }

    public function profile(Request $request)
    {
        $authClient = $this->resolveClientFromToken($request);

        if (!$authClient) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $client = $this->getClientData->execute($authClient->id);

        return response()->json([
            'client' => $this->presenter->present($client),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $authClient = $this->resolveClientFromToken($request);

        if (!$authClient) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'consent_personal_data' => ['sometimes', 'boolean'],
            'consent_marketing' => ['sometimes', 'boolean'],
        ]);

        $dto = new UpdateClientDTO(
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            birthDate: $data['birth_date'] ?? null,
            consentPersonalData: array_key_exists('consent_personal_data', $data) ? (bool) $data['consent_personal_data'] : null,
            consentMarketing: array_key_exists('consent_marketing', $data) ? (bool) $data['consent_marketing'] : null,
        );

        $client = $this->updateClient->execute($authClient->id, $dto);

        return response()->json([
            'client' => $this->presenter->present($client),
        ]);
    }

    public function addAddress(Request $request)
    {
        $authClient = $this->resolveClientFromToken($request);

        if (!$authClient) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $data = $request->validate([
            'type' => ['sometimes', 'string', 'in:default,additional'],
            'title' => ['nullable', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house' => ['required', 'string', 'max:255'],
            'entrance' => ['nullable', 'string', 'max:50'],
            'apartment' => ['nullable', 'string', 'max:50'],
            'make_default' => ['sometimes', 'boolean'],
        ]);

        $dto = new AddClientAddressDTO(
            clientId: $authClient->id,
            type: $data['type'] ?? 'additional',
            title: $data['title'] ?? null,
            street: $data['street'],
            house: $data['house'],
            entrance: $data['entrance'] ?? null,
            apartment: $data['apartment'] ?? null,
            makeDefault: array_key_exists('make_default', $data) ? (bool) $data['make_default'] : false,
        );

        $client = $this->addClientAddress->execute($dto);

        return response()->json([
            'client' => $this->presenter->present($client),
        ]);
    }

    public function deleteAddress(Request $request, int $id)
    {
        $authClient = $this->resolveClientFromToken($request);

        if (!$authClient) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $dto = new DeleteClientAddressDTO(
            clientId: $authClient->id,
            addressId: $id,
        );

        $client = $this->deleteClientAddress->execute($dto);

        return response()->json([
            'client' => $this->presenter->present($client),
        ]);
    }

    private function resolveClientFromToken(Request $request): ?UR_Client
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }

        /** @var PersonalAccessToken|null $accessToken */
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return null;
        }

        $tokenable = $accessToken->tokenable;

        if (!$tokenable instanceof UR_Client) {
            return null;
        }

        return $tokenable;
    }
}
