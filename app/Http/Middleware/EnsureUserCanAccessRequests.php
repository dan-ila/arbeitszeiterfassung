<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessRequests
{
    /**
     * Allow access to the requests pages for admins and managers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        if (!method_exists($user, 'canAccessRequests') || !$user->canAccessRequests()) {
            return redirect()->route('users.dashboard')->with('error', 'Keine Berechtigung.');
        }

        return $next($request);
    }
}
