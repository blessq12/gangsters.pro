<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRegisterRequest;
use App\Models\User;
use App\Services\LegalConsentService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
//
use Illuminate\Support\Facades\Mail;
use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class ApiClientAuthController extends Controller
{
    public function __construct(
        private LegalConsentService $legalConsents
    ) {
        $this->middleware('auth:sanctum', ['only' => [
            'updateUser',
            'getUser',
            'getUserData'
        ]]);
    }
    public function clientLogin(Request $request)
    {
        if (!User::where('email', $request->email)->exists()) {

            \App\Facades\TelegramMessage::sendMessage([
                '❌ Авторизация с несуществующим email' . "\n",
                '✉️ Email: ' . $request->email,
            ], 'error');

            return response()->json(['status' => false, 'message' => 'Нет учетной записи с этой почтой'], 401);
        }
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            \App\Facades\TelegramMessage::sendMessage([
                '❌ Неудачная попытка авторизации' . "\n",
                '✉️ Email: ' . $request->email,
            ], 'error');

            return response(['status' => false, 'message' => 'Неверный пароль'], 401);
        }

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('gangsta')->plainTextToken;

        return response([
            'status' => true,
            'user' => $user->only(['name', 'email', 'tel', 'dob']),
            'token' => $token
        ]);
    }
    public function clientRegister(Request $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response(['status' => false, 'message' => 'Пользователь с такой почтой уже существует'], 400);
        }
        if (User::where('tel', $request->tel)->exists()) {
            return response(['status' => false, 'message' => 'Пользователь с таким номером телефона уже существует'], 400);
        }

        $pdnDocumentId = (int) $request->input('pdn_document_id', 0);
        try {
            $this->legalConsents->assertPdnConsent($pdnDocumentId);
        } catch (InvalidArgumentException $e) {
            return response(['status' => false, 'message' => $e->getMessage()], 422);
        }

        $user = User::create($request->only(['name', 'email', 'tel', 'password']));

        if (!$user->save()) {
            \App\Facades\TelegramMessage::sendMessage([
                '❌ Регистрация нового пользователя с ошибкой' . "\n",
                '✉️ Email: ' . $user->email,
                '🔑 Пароль: ' . $user->password,
                '🔑 Ошибки: ' . $user->errors(),
            ], 'error');
            return response(['status' => false, 'errors' => $user->errors(), 'message' => 'Не удалось зарегистрировать пользователя'], 400);
        }

        $this->legalConsents->recordRegisterPdnConsent($user, $pdnDocumentId, $request);

        \App\Facades\TelegramMessage::sendMessage([
            '✅ Регистрация нового пользователя: ' . $user->email . "\n",
            '👤 Имя: ' . $user->name,
            '📞 Телефон: ' . $user->tel,
        ], 'event');

        Mail::to($user->email)->send(new RegisterMail($user));

        return response([
            'status' => true,
            'user' => $user->only(['name', 'email', 'tel', 'dob']),
            'token' => $user->createToken('gangsta')->plainTextToken
        ], 200);
    }

    public function resetPassword(Request $request)
    {

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response([
                'status' => false,
                'message' => 'Пользователь с таким email не найден'
            ], 404);
        }

        $user->token_to_reset_password = Str::random(18);
        $user->save();

        Mail::to($user->email)->send(new \App\Mail\RequestToResetPassword($user->name, $user->token_to_reset_password));

        return response([
            'status' => true,
            'message' => 'Ссылка для сброса пароля отправлена на вашу почту',
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user = auth('sanctum')->user();
        $user->update([
            'name' => $request->name,
            'tel' => $request->tel,
            'dob' => Carbon::parse($request->dob),
        ]);

        $user->dob = Carbon::parse($user->dob)->format('d-m-Y');

        return response([
            'status' => true,
            'user' => $user
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
        ], [
            'password.required' => 'Пароль обязателен',
            'password.string' => 'Пароль должен быть строкой',
            'password.min' => 'Пароль должен быть длиннее 6 символов',
        ]);

        if ($validated->fails()) {
            return response([
                'status' => false,
                'message' => $validated->errors()->first()
            ], 400);
        }

        $user = User::where('token_to_reset_password', $request->token)->first();

        if (!$user) {
            return response([
                'status' => false,
                'message' => 'Неверный токен'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->token_to_reset_password = null;

        if (!$user->save()) {
            return response([
                'status' => false,
                'message' => 'Не удалось изменить пароль'
            ], 500);
        }

        return response([
            'status' => true,
            'message' => 'Пароль успешно изменен'
        ], 200);
    }

    public function getUser()
    {
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $user->dob = Carbon::parse($user->dob)->format('d-m-Y');

        return response()->json([
            'user' => $user,
        ]);
    }

    public function getUserData()
    {
        $user = auth('sanctum')->user();
        $user->dob = Carbon::parse($user->dob)->format('d-m-Y');
        return response()->json([
            'user' => $user,
        ]);
    }

    public function addAddress(Request $request)
    {
        $user = auth('sanctum')->user();
        $user->addresses()->create(
            $request->all()
        );
        return response()->json(['message' => 'Address added successfully']);
    }

    public function deleteAddress($id)
    {
        $user = auth('sanctum')->user();
        $user->addresses()->where('id', $id)->delete();
        return response()->json(['message' => 'Address deleted successfully']);
    }
}
