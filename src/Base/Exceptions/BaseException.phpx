<?php

namespace Kfn\Base\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Kfn\Base\Enums\ResponseCode;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseException extends \Exception
{
    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \JsonException
     */
    public function render($request, \Throwable $e): Response|JsonResponse|SymfonyResponse
    {
        if ((false === $e instanceof KfnException) && $request->is('api/*')) {
            $e = self::mapToException($request, $e);
        }

        return $e->toResponse($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     *
     * @return \Kfn\Base\Exceptions\KfnException|\Throwable
     */
    public static function mapToException(Request $request, \Throwable $e): KfnException|\Throwable
    {
        if ($e instanceof ModelNotFoundException) {
            return new KfnException(ResponseCode::ERR_ENTITY_NOT_FOUND, ResponseCode::ERR_ENTITY_NOT_FOUND->message(), previous: $e);
        }

        if ($e instanceof ValidationException) {
            return new KfnException(ResponseCode::ERR_VALIDATION, $e->getMessage(), $e->errors(), previous: $e);
        }

        if ($e instanceof \Spatie\Permission\Exceptions\RoleAlreadyExists) {
            return new KfnException(ResponseCode::ERR_VALIDATION, $e->getMessage(), previous: $e);
        }

        if ($e instanceof AuthenticationException) {
            return new KfnException(ResponseCode::ERR_AUTHENTICATION, $e->getMessage(), null, previous: $e);
        }

        if ($e instanceof NotFoundHttpException) {
            return new KfnException(ResponseCode::ERR_ROUTE_NOT_FOUND, $e->getMessage(), null, previous: $e);
        }

        if ($e instanceof AuthorizationException || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return new KfnException(ResponseCode::ERR_ACTION_UNAUTHORIZED, $e->getMessage(), null, previous: $e);
        }

        // if ($e instanceof DumpAPIException){
        //     return $e;
        // }

        return new KfnException(
            rc: ResponseCode::ERR_UNKNOWN,
            message: 'Something went wrong',
            data: [
                'base_url' => $request->getBaseUrl(),
                'path' => $request->getUri(),
                'origin' => $request->ip(),
                'method' => $request->getMethod(),
            ],
            previous: $e
        );
    }
}
