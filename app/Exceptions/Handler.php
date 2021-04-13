<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Http\Response;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        // \League\OAuth2\Server\Exception\OAuthServerException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        \DB::rollback();
        $rendered = parent::render($request, $e);
        // return $rendered;
        $msg = $this->getFixedMessage($e);
        $responseError = [
            '_code' => $rendered->getStatusCode(),
            '_meta' => [],
        ];
        if( is_array($msg) ){
            $responseError = array_merge( $responseError, $msg );
        }else{
            $responseError['message'] = $msg;
        }
        return response()->json($responseError, $rendered->getStatusCode());
    }
    private function getFixedMessage($e){
        if( isJson($e->getMessage()) ){
            return json_decode( $e->getMessage(), true );
        }
        $fileName = explode( (str_contains($e->getFile(), "\\")?"\\":"/"), $e->getFile());
        $stringMsg = $e->getMessage();
        $stringMsg = $stringMsg === null || $stringMsg == ""? "Maybe Server Error" : $stringMsg;
        // $stringMsg = !str_contains( $stringMsg, "SQLSTATE" ) && ( env("APP_DEBUG",false) || !empty( app()->request->header("Debugger") )) ? $stringMsg : "Maybe Server Error";
        $msg = $stringMsg.(env("APP_DEBUG",false)?" => file: ".str_replace(".php","",end($fileName))." line: ".$e->getLine():"");
        return $msg;
    }
}
