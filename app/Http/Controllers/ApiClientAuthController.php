<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRegisterRequest;
use App\Mail\GreetingMessageWithPassword;
use App\Mail\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// 
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;

class ApiClientAuthController extends Controller
{
    public function clientLogin(Request $request)
    {
        if (!User::where('email', $request->email)->exists()) {
            return response()->json(
                [
                    'status' => false,
                    'errors' => [
                        (object) [
                            'name' => 'account',
                            'message' => 'Нет учетной записи с этой почтой'
                        ]
                    ]
                ],
                401
            );
        }
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
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

        return response([
            'status' => true,
            'user' => $user->only(['name', 'email', 'tel', 'dob']),
            'token' => $user->tokens()->first()->token
        ]);
    }
    public function clientRegister(ApiRegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create($validated);

        if (!$user->save()) {
            return response([
                'status' => false,
                'errors' => $user->errors()
            ], 400);
        }

        Mail::to($user->email)->send(new RegisterMail($user));

        return response([
            'status' => true,
            'user' => $user,
            'token' => $user->createToken('gangsta')->plainTextToken
        ], 200);
    }

    public function getUser(Request $request)
    {
        $user = auth('sanctum')->user();
        $user->dob = Carbon::parse($user->dob)->format('d-m-Y');
        if ($user) {
            return response([
                'status' => true,
                'user' => $user
            ], 200);
        } else {
            return response([
                'status' => false,
                'message' => 'Пользователь с таким токеном не найден'
            ], 401);
        }
    }

    public function resetPassword(Request $request)
    {

        $user = User::where('email', $request->email)->first();
        $password = Str::random(16);
        if (!$user) {
            return response([
                'status' => false,
                'message' => 'Пользователь с таким email не найден'
            ], 404);
        }

        $user->password = Hash::make($password);
        $user->save();

        Mail::to($user->email)->send(new ResetPassword($password));

        return response([
            'status' => true,
            'message' => 'Письмо с новым паролем отправлено на вашу почту'
        ]);
    }

    public function updateUser(Request $request)
    {
        $user = auth('sanctum')->user();
        $user->update([
            'name' => $request->name,
            'tel' => $request->tel,
            'dob' => Carbon::parse($request->dob),
        ]);
        // Format dob to 'd-m-Y' before returning
        $user->dob = Carbon::parse($user->dob)->format('d-m-Y');
        // Return the response with the formatted dob
        return response([
            'status' => true,
            'user' => $user
        ], 200);
    }
}
