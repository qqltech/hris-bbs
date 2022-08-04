<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Cache;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if( env("AUTHORIZATION", false) ){
            if ($this->auth->guard($guard)->guest()) {
                return response('Unauthorized.', 401);
            }
        }

        //  cache USER who is requesting, use getTrackedUser( $userId ) to retrieve
        if( ($cacheTime = env("USER_TRACK_CACHE_SECONDS", 0))>0 ){
            $key = 'track-user-'.(\Auth::user()->id);
            $headers = $request->header();
            unset( $headers['authorization'] );
            Cache::put( $key, [
                'ip'=>$request->ip(),
                'agent' => $request->userAgent(),
                'payload'=> $request->all(),
                'at'=>\Carbon::now()->format('d/m/Y H:i:s'),
                'route'=>$request->url(),
                'headers'=>$headers
            ], $cacheTime );
        }

        //  cache GET /:modelname/:id, use getTrackedRow( $model, $id ) to retrieve
        if( $request->isMethod('GET') && !$request->route('detailmodelname') && ($model=$request->route('modelname')) && ($id=$request->route('id')) && ($cacheTime = env("FIND_TRACK_CACHE_SECONDS", 0))>0){
            $key = "track-$model-$id";
            $headers = $request->header();
            unset( $headers['authorization'] );

            Cache::put( $key, [
                'ip'=>$request->ip(),
                'agent' => $request->userAgent(),
                'payload'=> $request->all(),
                'at'=>\Carbon::now()->format('d/m/Y H:i:s'),
                'headers'=>$headers,
                'user'=>\Auth::user()
            ], $cacheTime );
        }

        return $next($request);
    }
}
