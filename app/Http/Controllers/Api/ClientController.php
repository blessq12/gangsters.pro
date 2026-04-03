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
use App\Application\Client\Query\GetClientDataUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
    ) {
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
            'consent_personal_data' => ['required', 'boolean'],
            'consent_marketing' => ['required', 'boolean'],
        ]);

        $dto = new RegisterDTO(
            name: $data['name'],
            phone: $data['phone'],
            email: $data['email'],
            birthDate: $data['birth_date'] ?? null,
            password: $data['password'] ?? null,
            consentPersonalData: (bool) $data['consent_personal_data'],
            consentMarketing: (bool) $data['consent_marketing'],
        );

        $result = $this->registerClient->execute($dto);

        return response()->json($result);
    }

    public function login(Request $request)
    {
        $data = validator($request->all(), [
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email'],
            'password' => ['required', 'string'],
        ])->after(function ($validator) {
            $d = $validator->getData();
            $hasPhone = filled($d['phone'] ?? null);
            $hasEmail = filled($d['email'] ?? null);
            if (! $hasPhone && ! $hasEmail) {
                $validator->errors()->add('phone', 'Phone or email is required');
            }
            if ($hasPhone && $hasEmail) {
                $validator->errors()->add('phone', 'Provide only phone or email.');
                $validator->errors()->add('email', 'Provide only phone or email.');
            }
        })->validate();

        $dto = new LoginDTO(
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'],
        );

        $result = $this->loginClient->execute($dto);

        return response()->json($result);
    }

    public function forgotPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $dto = new RequestPasswordResetDTO(
            email: $data['email'],
        );

        $this->requestPasswordReset->execute($dto);

        return response()->json([
            'status' => true,
            'message' => 'Password reset instructions sent',
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

        $result = $this->changePassword->execute($dto);

        return response()->json([
            'status' => true,
            'client' => $result['client'],
        ]);
    }

    public function profile()
    {
        $result = $this->getClientData->execute();

        return response()->json($result);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255'],
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

        $result = $this->updateClient->execute($dto);

        return response()->json($result);
    }

    public function addAddress(Request $request)
    {
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
            type: $data['type'] ?? 'additional',
            title: $data['title'] ?? null,
            street: $data['street'],
            house: $data['house'],
            entrance: $data['entrance'] ?? null,
            apartment: $data['apartment'] ?? null,
            makeDefault: array_key_exists('make_default', $data) ? (bool) $data['make_default'] : false,
        );

        $result = $this->addClientAddress->execute($dto);

        return response()->json($result);
    }

    public function deleteAddress(Request $request, int $id)
    {
        $dto = new DeleteClientAddressDTO(
            addressId: $id,
        );

        $result = $this->deleteClientAddress->execute($dto);

        return response()->json($result);
    }
}
