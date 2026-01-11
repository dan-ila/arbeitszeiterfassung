<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response{
        $user = Auth::user();
        if (!$user) {
            return redirect('/login');
        }
        if (!$user->isAdmin()) {
            return redirect()->route('users.dashboard')->with('error', 'Keine Berechtigung.');
        }
        return $next($request);
    }   
}
