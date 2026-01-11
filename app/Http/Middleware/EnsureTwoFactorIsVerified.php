<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureTwoFactorIsVerified
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Only enforce 2FA if the user actually enabled it.
        if (!$user || !$user->two_factor_enabled) {
            return $next($request);
        }

        if (!session('2fa_passed')) {
            return redirect()->route('2fa.challenge');
        }

        return $next($request);
    }

}
