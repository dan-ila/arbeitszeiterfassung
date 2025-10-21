<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login'); 
    }

    public function login(Request $request)
    { 
        $request->validate([
            "email" => "required|email|exists:users,email",
            "password" => "required|string",
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user); 
            $request->session()->regenerate(); 

            return redirect()->route('users.dashboard');
        } else {
            return back()->with('error', 'Invalid credentials');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return view('auth.login');
    }
}
