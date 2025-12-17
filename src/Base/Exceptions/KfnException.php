<?php

namespace Kfn\Base\Exceptions;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use JsonException;
use Kfn\Base\Contracts\IKfnException;
use Kfn\Base\Contracts\IResponse;
use Kfn\Base\Contracts\IResponseCode;
use Kfn\Base\Enums\ResponseCode;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class KfnException extends \Exception implements IKfnException, Arrayable, Responsable
{
    /** @var string */
    public static string $result;

    /** @var Closure|null */
    private static Closure|null $customResult = null;

    /**
     * Base Exception constructor.
     *
     * @param  IResponseCode  $rc
     * @param  string|null  $message
     * @param  array|null  $data
     * @param  array|null  $errors
     * @param  Throwable|null  $previous
     */
    public function __construct(
        public IResponseCode $rc = ResponseCode::ERR_UNKNOWN,
        string|null $message = null,
        protected array|null $data = null,
        protected array|null $errors = null,
        Throwable|null $previous = null
    ) {
        if (is_null($message)) {
            $message = $rc->message();
        }
        parent::__construct($message, 0, $previous);
    }

    /**
     * {@inheritDoc}
     *
     * @param $request
     *
     * @return JsonResponse|Response
     * @throws JsonException
     */
    public function toResponse($request)
    {
        if ($request->acceptsHtml()) {
            abort($this->rc->httpCode(), $this->getResponseMessage());
        }

        return $request->expectsJson()
            ? response()->json($this->toArray(), $this->rc->httpCode())
            : response()->make(json_encode($this->toArray(), JSON_THROW_ON_ERROR))
                ->withException($this);
    }

    /** {@inheritdoc} */
    public function toArray(): array
    {
        if (static::$customResult instanceof Closure) {
            return call_user_func(static::$customResult, $this);
        }

        $resp = [
            'rc' => $this->getResponseCode(),
            'message' => $this->getResponseMessage(),
            'timestamp' => now(),
            \Kfn\Base\Response::getResultAs()->dataWrapper() => $this->data,
        ];

        if ($this->errors) {
            $resp['errors'] = $this->errors;
        }

        if (config('app.debug') && $this->getPrevious() instanceof Throwable) {
            $resp['debug'] = [
                'origin_message' => $this->getPrevious()->getMessage(),
                'class' => get_class($this->getPrevious()),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine(),
                'trace' => $this->getPrevious()->getTrace(),
            ];
        }

        return $resp;
    }

    /**
     * Get response code.
     *
     * @return string
     */
    public function getResponseCode(): string
    {
        return $this->rc->name;
    }

    /**
     * Get a response message.
     *
     * @return string
     */
    public function getResponseMessage(): string
    {
        if (config('app.debug') && $this->getPrevious() instanceof Throwable) {
            return $this->getPrevious()->getMessage();
        }

        if ($this->message) {
            return $this->message;
        }

        return $this->rc->message();
    }

    /**
     * @param Request $request
     * @param Throwable $e
     *
     * @return IResponse|JsonResponse|SymfonyResponse
     * @throws BindingResolutionException
     * @throws JsonException
     */
    public static function renderException(Request $request, Throwable $e): IResponse|JsonResponse|SymfonyResponse
    {
        $apiPrefixes = collect((array) config('koffinate.base.api_prefixes', []));
        $apiPrefixes->each(fn ($it) => $apiPrefixes->add($it.'/*'));

        if ((! $e instanceof static) && ($request->is($apiPrefixes->toArray()) || $request->ajax())) {
            $e = static::mapToException($request, $e);
        }

        return $e->toResponse($request)
            ->withHeaders(['Accept' => 'application/json'])
            ->send();
    }

    /**
     * @param Request $request
     * @param Throwable $e
     *
     * @return Throwable|static
     */
    public static function mapToException(Request $request, Throwable $e): static|Throwable
    {
        // try {
        //     $statusCode = $e->getStatusCode();
        // } catch (\Throwable) {
        //     $statusCode = 0;
        // }

        // if ($statusCode == ResponseCode::ERR_EXPIRED_TOKEN->httpCode() || $e->getPrevious() instanceof TokenMismatchException) {
        if ($e->getPrevious() instanceof TokenMismatchException) {
            return new static(ResponseCode::ERR_EXPIRED_TOKEN, $e->getMessage(), previous: $e);
        }

        if ($e instanceof ModelNotFoundException) {
            return new static(ResponseCode::ERR_ENTITY_NOT_FOUND, ResponseCode::ERR_ENTITY_NOT_FOUND->message(), previous: $e);
        }

        if ($e instanceof ValidationException) {
            return new static(ResponseCode::ERR_VALIDATION, $e->getMessage(), $e->errors(), previous: $e);
        }

        if ($e instanceof \Spatie\Permission\Exceptions\RoleAlreadyExists) {
            return new static(ResponseCode::ERR_VALIDATION, $e->getMessage(), previous: $e);
        }

        if ($e instanceof AuthenticationException) {
            return new static(ResponseCode::ERR_AUTHENTICATION, $e->getMessage(), null, previous: $e);
        }

        if ($e instanceof NotFoundHttpException) {
            return new static(ResponseCode::ERR_ROUTE_NOT_FOUND, $e->getMessage(), null, previous: $e);
        }

        if ($e instanceof AuthorizationException || $e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return new static(ResponseCode::ERR_ACTION_UNAUTHORIZED, $e->getMessage(), null, previous: $e);
        }

        // if ($e instanceof DumpAPIException){
        //     return $e;
        // }

        return new static(
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

    /**
     * @param  Closure(static): void  $customResult
     *
     * @return void
     */
    public static function setCustomResult(Closure $customResult): void
    {
        self::$customResult = $customResult;
    }
}
