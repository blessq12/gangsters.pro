<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRegisterRequest;
use App\Mail\GreetingMessageWithPassword;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ApiClientAuthController extends Controller
{
    public function clientLogin(Request $request)
    {
        if (!User::where('tel', $request->tel)->exists()) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => [
                        (object) [
                            'name' => 'account',
                            'message' => 'Нет учетной записи с этим номером'
                        ]
                    ]
                ],
                401
            );
        }
        if (!Auth::attempt(['tel' => $request->tel, 'password' => $request->password])) {
            return response([
                'status' => false,
                'errors' => [
                    (object) [
                        'name' => 'account',
                        'message' => 'Неверный пароль'
                    ]
                ]
            ], 401);
        }
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('gangsta');

        return response([
            'status' => true,
            'user' => $user,
            'token' => $token->plainTextToken
        ]);
    }
    public function clientRegister(ApiRegisterRequest $request)
    {
        $password = Str::random(16);
        $validated = $request->validated();
        $user = new User($validated);
        $user->password = Hash::make($password);

        if ($user->save()) {
            Mail::to($user->email)->send(new GreetingMessageWithPassword($user, $password));
        }

        $token = $user->createToken('gangsta');

        return response([
            'status' => true,
            'user' => User::find($user->id),
            'token' => $token->plainTextToken
        ]);
    }

    public function getUser(Request $request)
    {
        if (auth('sanctum')->user()) {
            return response([
                'status' => true,
                'user' => auth('sanctum')->user()
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'Пользователь с таким токеном не найден'
            ], 401);
        }
    }
}
