<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Analytic
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $redis = Redis::connection();
        $currentRoute = Route::getCurrentRoute();
        if ($currentRoute && $currentRoute->methods[0] === 'GET') {
            $now = date('Y-m-d h:i:s');
            $ip = $request->ip();
            $page = $request->path();
            if (!Str::contains($page, ['login', 'logout', 'change-language'])) {
                if (Redis::exists($ip . $page)) {

                    return $next($request);
                } else {
                    $redis->set($ip . $page, true, 'EX', 300);
                    \App\Models\Analytic::create([
                        'time' => $now,
                        'ip' => $ip,
                        'page' => $page
                    ]);
                }
            }
        };


        return $next($request);
    }
}
