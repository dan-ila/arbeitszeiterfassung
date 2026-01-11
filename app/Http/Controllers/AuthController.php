<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required|string",
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'E-Mail oder Passwort ist falsch.'])
                ->withInput($request->only('email'));
        }

        Auth::login($user);
        $request->session()->regenerate();

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'login',
            'details' => 'User logged in.',
        ]);


        return redirect()->route('users.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // Show the password set form
    public function showSetPasswordForm(Request $request, string $token)
    {
        return view('auth.setPassword', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // Handle password submission
    public function setPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = \Illuminate\Support\Facades\Hash::make($password);
                $user->save();
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Passwort wurde erfolgreich gesetzt.')
            : back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
    }
}
