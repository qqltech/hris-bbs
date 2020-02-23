<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;



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
    public function render($request, Exception $exception)
    {
        $e = $exception;
        // if (!$request->ajax()) {
            return parent::render($request, $e);
        // }
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof ValidationException && $e->getResponse()) {
            return $e->getResponse();
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $response_code = Response::HTTP_METHOD_NOT_ALLOWED;
            $e = new MethodNotAllowedHttpException([], 'Metode request tidak diperbolehkan.', $e);
        } elseif ($e instanceof ModelNotFoundException) {
            $response_code = Response::HTTP_NOT_FOUND;
            $e = new NotFoundHttpException('Data tidak ditemukan.', $e);
        } elseif ($e instanceof NotFoundHttpException) {
            $response_code = Response::HTTP_NOT_FOUND;
            $e = new NotFoundHttpException('Endpoint service tidak ditemukan.', $e);
        } elseif ($e instanceof AuthorizationException) {
            $response_code = Response::HTTP_UNAUTHORIZED;
            $e = new AuthorizationException('Anda tidak diperbolehkan mengakses endpoint service ini.', $response_code);
        } else {
            $response_code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $e = new HttpException($response_code, 'Terjadi kesalahan di sistem internal, hubungi administrator.');
        }

        logTg("developer", (\Auth::check()?\Auth::user()->name:"Guest")."'s Error: ".(!in_array($exception->getMessage(),["",null])?$exception->getMessage():$e->getMessage()));
        return response()->json([
            'response_code' => $response_code,
            'response_message' => !in_array($exception->getMessage(),["",null])?$exception->getMessage():$e->getMessage()
        ], $response_code);
    }
}
