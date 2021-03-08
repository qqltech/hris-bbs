<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exceptions\HttpResponseException;
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
        \League\OAuth2\Server\Exception\OAuthServerException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e)
    {

        \DB::rollback();
        $rendered = parent::render($request, $e);
        return $rendered;
        $msg = $this->getFixedMessage($e);
        $response = [
            'code' => $rendered->getStatusCode()
        ];
        if( gettype( $msg )== "string" ){
            $response['errors'] = [ $msg ];
        }else{
            $response =  array_merge($response, (array)$msg);
        }
        return response()->json( $response, $rendered->getStatusCode());
    }
    private function getFixedMessage($e){
        if( isJson($e->getMessage()) ){
            return json_decode( $e->getMessage() );
        }
        $fileName = explode( (strpos($e->getFile(), "\\")!==false?"\\":"/"), $e->getFile());
        $stringMsg = $e->getMessage();
        $stringMsg = $stringMsg === null || $stringMsg == ""? "Maybe Server Error" : $stringMsg;
        $msg = $stringMsg.(env("APP_DEBUG",false)?" => file: ".str_replace(".php","",end($fileName))." line: ".$e->getLine():"");
        return $msg;
    }
}
