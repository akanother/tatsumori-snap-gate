<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoStore
{
    public function handle(Request $request, Closure $next)
    {
        $res = $next($request);
        return $res
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
