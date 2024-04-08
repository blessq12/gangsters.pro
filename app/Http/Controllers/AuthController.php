<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }
    public function userLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'email|required',
            'password' => 'min:6|required'
        ]);
        if (!User::where('email', $validated['email'])->exists()) {
            return back()->withErrors(['user' => 'Пользователь с таким email несуществует']);
        }
        if (!User::where('email', $validated['email'])->first()->admin) {
            return abort(403);
        }
        if (!Auth::attempt($validated)) {
            return back()->withErrors(['user' => 'Неверный пароль']);
        }
        return redirect()->route('crm.index');
    }
    public function registerPage()
    {
        return view('auth.register');
    }
    public function userRegister(Request $request)
    {

        $validated = $request->validate([
            'email' => 'email|required|unique:users',
            'name' => 'required|min:3',
            'tel' => 'required|min:18|max:18|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'password' => Hash::make($validated['password'])
        ]);

        if (!$user->save()) {
            return back()->withErrors(['user' => 'Не удалось создать пользователя'])->withInput();
        }

        return redirect()->route('auth.login-page')->with('success', 'Учетная запиись создана, ожидайте подтверждения модератором');
    }
    public function userLogout(Request $request)
    {
        Auth::logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('main.index');
    }
    /**
     * API METHODS
     */

    public function apiLogin(Request $request)
    {
        return response()->json($request, 200);
    }
    public function apiRegister(Request $request)
    {
        return response()->json($request, 200);
    }
    public function getUser()
    {
        return 'user';
    }
}
