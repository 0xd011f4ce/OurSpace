<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateOnlineUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth ()->check ())
            return $next($request);

        $user = auth ()->user ();

        if (Cache::has ("user-online-" . $user->id))
            return $next($request);

        $expires_at = now ()->addMinutes (5);
        Cache::put ("user-online-" . $user->id, true, $expires_at);

        $user->last_online_at = now ();
        $user->save ();

        return $next($request);
    }
}
