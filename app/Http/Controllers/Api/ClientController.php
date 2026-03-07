<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiClientAuthController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
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
        return app(ApiClientAuthController::class)->clientRegister($request);
    }

    public function login(Request $request)
    {
        return app(ApiClientAuthController::class)->clientLogin($request);
    }

    public function forgotPassword(Request $request)
    {
        return app(ApiClientAuthController::class)->resetPassword($request);
    }

    public function changePassword(Request $request)
    {
        return app(ApiClientAuthController::class)->changePassword($request);
    }

    public function profile(Request $request)
    {
        return app(ApiClientAuthController::class)->getUserData($request);
    }

    public function updateProfile(Request $request)
    {
        return app(ApiClientAuthController::class)->updateUser($request);
    }

    public function addAddress(Request $request)
    {
        return app(ApiClientAuthController::class)->addAddress($request);
    }

    public function deleteAddress(Request $request, int $id)
    {
        return app(ApiClientAuthController::class)->deleteAddress($id);
    }
}
