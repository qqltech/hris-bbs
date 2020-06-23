<?php

namespace App\Http\Middleware;

use Closure;

class Laradev
{
    public function handle($request, Closure $next)
    {
        if ( $request->header('laradev')==null || $request->header('laradev')!='quantumleap150671') {
            return response()->json(['status'=>'unauthorized'], 401);
        }
        return $next($request);
    }
}
