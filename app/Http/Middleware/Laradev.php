<?php

namespace App\Http\Middleware;

use Closure;

class Laradev
{
    public function handle($request, Closure $next)
    {
        if ( $request->header('laradev')==null || $request->header('laradev')!=env("LARADEVPASSWORD","bismillah") ) {
            return response()->json(['status'=>'unauthorized'], 401);
        }
        return $next($request);
    }
}
