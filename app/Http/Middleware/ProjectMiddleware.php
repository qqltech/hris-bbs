<?php

namespace App\Http\Middleware;

use Closure;

class ProjectMiddleware
{
    public function handle($request, Closure $next)
    {
      
      if( env("MIDDLEWARE_RESOLVER") ){
        $funcArr = explode(".", env("MIDDLEWARE_RESOLVER"));
        $class = getCore($funcArr[0]) ?? getCustom($funcArr[0]);
        $func = $funcArr[1];
        return $class->$func($request, $next);
      }
    
      return $next($request);
    }
}
