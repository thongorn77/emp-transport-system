<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Models\Driver;
use Symfony\Component\HttpFoundation\Response;

class DriverAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $lineId = $request->cookie('driver_auth');

        if (!$lineId) {
            return redirect()->route('driver.register');
        }

        $driver = Driver::where('line_user_id', $lineId)->first();

        if (!$driver) {
            Cookie::queue(Cookie::forget('driver_auth'));
            return redirect()->route('driver.register');
        }

        $request->attributes->set('driver', $driver);
        view()->share('authDriver', $driver);

        return $next($request);
    }
}
