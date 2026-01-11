<?php

namespace App\Http\Middleware;

use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceIsRegistered
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $token = $request->json('device_token'); 

        $device = Device::where('api_token', $token)->first();

        if (!$device) {
            return response()->json(['message' => 'Unauthorized device'], 401);
        }

        if ($device->getAttribute('enabled') === false) {
            return response()->json(['message' => 'Device disabled'], 403);
        }

        $request->attributes->set('device', $device);

        return $next($request);
    }
}
