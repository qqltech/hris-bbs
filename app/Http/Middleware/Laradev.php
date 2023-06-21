<?php

namespace App\Http\Middleware;

use Closure;

class Laradev
{
    public function handle($request, Closure $next)
    {
        $ori = ($request->header('sec-fetch-site')??'same-site')=='same-site';
        if ( !($devToken=$request->header('developer-token')) && !$ori && $request->header('laradev')==null || $request->header('laradev')!=env("LARADEVPASSWORD","bismillah") ) {
            return response()->json(['status'=>'unauthorized'], 401);
        }
        // if($ori) $devToken = '';
        
        $frontenders = explode(",", env('DEV_FRONTENDERS', ''));
        $backenders = explode(",", env('DEV_BACKENDERS', ''));
        $owners = explode(",", env('DEV_OWNERS', 'devganteng0011'));
        
        if( !$ori && !in_array($devToken, $frontenders) && !in_array($devToken, $backenders) && !in_array($devToken, $owners) ){
            return response()->json(['status'=>'unauthorized'], 401);
        }

        config(['developer'=> $devToken]);
        if( in_array($devToken, $frontenders) ){
            config(['devrole'=> 'frontend']);
        }elseif( in_array($devToken, $backenders) ){
            config(['devrole'=> 'backend']);
        }elseif( $ori || in_array($devToken, $owners) ){
            config(['devrole'=> 'owner']);
        }
        
        return $next($request);
    }
}
