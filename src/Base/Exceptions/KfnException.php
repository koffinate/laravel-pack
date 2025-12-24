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
use Illuminate\Support\Uri;
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
        if ($request->acceptsHtml() && ! static::shouldRenderException()) {
            if ('redirect' === config('koffinate.ui.exception.handling_method')) {
                $redirectTo = config('koffinate.ui.exception.redirect_to');

                $prevUri = new Uri($request->headers->get('referer') ?? '');
                if ('back' === $redirectTo) {
                    $redirect = redirect()->back();
                    $prevPath = $prevUri->path();
                } else {
                    $redirect = redirect()->to($redirectTo);
                    $redirectUri = new Uri($redirectTo ?? '');
                    $prevPath = $redirectUri->path();
                }

                if (
                    $request->path() === $prevPath ||
                    $request->getHttpHost() !== $prevUri->getUri()->getAuthority()
                ) {
                    $redirect = redirect()->to('/');
                }
                // session()->flash('kfn-exception', $this->toArray());

                try {
                    $exceptionData = [
                        'rc' => $this->rc::class,
                        'name' => $this->rc->name,
                        'statusCode' => $this->rc->httpCode(),
                        'statusText' => $this->rc->statusText(),
                        'message' => $this->getResponseMessage(),
                    ];

                    $redirect->withInput()
                        ->with('kfn-exception', $exceptionData)
                        ->send();
                } catch (\Throwable $e) {
                    // continue to the next handler
                }
            }

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

        $message = $this->getResponseMessage();
        if (! hasDebugModeEnabled() && str($message)->contains(['SQLSTATE', 'No query results'], true)) {
            $message = 'Query data not found';
        }

        $resp = [
            'rc' => $this->getResponseCode(),
            'message' => $message,
            'timestamp' => now(),
            \Kfn\Base\Response::getResultAs()->dataWrapper() => $this->data,
        ];

        if ($this->errors) {
            $resp['errors'] = $this->errors;
        }

        $previousExc = $this->getPrevious();
        if (hasDebugModeEnabled() && $previousExc instanceof Throwable) {
            $resp['debug'] = [
                'origin_message' => $previousExc->getMessage(),
                'class' => get_class($previousExc),
                'file' => $previousExc->getFile(),
                'line' => $previousExc->getLine(),
                'trace' => match (config('koffinate.base.debug.trace_mode')) {
                    'string' => $previousExc->getTraceAsString(),
                    'array' => explode(PHP_EOL, $previousExc->getTraceAsString()),
                    default => $previousExc->getTrace(),
                },
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
        if (hasDebugModeEnabled() && $this->getPrevious() instanceof Throwable) {
            return $this->getPrevious()->getMessage();
        }

        if ($this->message) {
            return $this->message;
        }

        return $this->rc->message();
    }

    /**
     * @return bool
     */
    public static function shouldRenderException(): bool
    {
        $request = request();
        $apiPrefixes = collect((array) config('koffinate.base.api_prefixes', []));
        $apiPrefixes->each(fn ($it) => $apiPrefixes->add($it.'/*'));

        return $request->is($apiPrefixes->toArray()) || $request->ajax();
    }

    /**
     * @param  Throwable  $throwable
     * @param  Request  $request
     * @param  Closure|null  $unrenderable
     *
     * @return mixed|void
     * @throws JsonException
     * @throws BindingResolutionException
     */
    public static function renderable(Throwable $throwable, Request $request, Closure|null $unrenderable = null)
    {
        if (static::shouldRenderException()) {
            if (! $throwable instanceof IKfnException) {
                $throwable = static::mapToException($request, $throwable);
            }

            \Kfn\Base\Exceptions\KfnException::renderException($request, $throwable, false);
        }
        if ($unrenderable instanceof Closure) {
            return call_user_func($unrenderable, $throwable, $request);
        }
    }

    /**
     * @param  Request  $request
     * @param  Throwable  $throwable
     * @param  bool  $checkThrowable
     *
     * @return IResponse|JsonResponse|SymfonyResponse
     * @throws JsonException
     */
    public static function renderException(
        Request $request,
        Throwable $throwable,
        bool $checkThrowable = true
    ): IResponse|JsonResponse|SymfonyResponse {
        if ($checkThrowable && ! $throwable instanceof static && static::shouldRenderException()) {
            $throwable = static::mapToException($request, $throwable);
        }

        return $throwable->toResponse($request)
            ->withHeaders(['Accept' => 'application/json'])
            ->send();
    }

    /**
     * @param Request $request
     * @param Throwable $throwable
     *
     * @return Throwable|static
     */
    public static function mapToException(Request $request, Throwable $throwable): static|Throwable
    {
        // try {
        //     $statusCode = $throwable->getStatusCode();
        // } catch (\Throwable) {
        //     $statusCode = 0;
        // }

        // if ($statusCode == ResponseCode::ERR_EXPIRED_TOKEN->httpCode() || $throwable->getPrevious() instanceof TokenMismatchException) {
        if ($throwable->getPrevious() instanceof TokenMismatchException) {
            return new static(ResponseCode::ERR_EXPIRED_TOKEN, $throwable->getMessage(), previous: $throwable);
        }

        if ($throwable instanceof ModelNotFoundException) {
            return new static(ResponseCode::ERR_ENTITY_NOT_FOUND, ResponseCode::ERR_ENTITY_NOT_FOUND->message(), previous: $throwable);
        }

        if ($throwable instanceof ValidationException) {
            return new static(ResponseCode::ERR_VALIDATION, $throwable->getMessage(), $throwable->errors(), previous: $throwable);
        }

        if ($throwable instanceof \Spatie\Permission\Exceptions\RoleAlreadyExists) {
            return new static(ResponseCode::ERR_VALIDATION, $throwable->getMessage(), previous: $throwable);
        }

        if ($throwable instanceof AuthenticationException) {
            return new static(ResponseCode::ERR_AUTHENTICATION, $throwable->getMessage(), null, previous: $throwable);
        }

        if ($throwable instanceof NotFoundHttpException) {
            return new static(ResponseCode::ERR_ROUTE_NOT_FOUND, $throwable->getMessage(), null, previous: $throwable);
        }

        if ($throwable instanceof AuthorizationException || $throwable instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return new static(ResponseCode::ERR_ACTION_UNAUTHORIZED, $throwable->getMessage(), null, previous: $throwable);
        }

        // if ($throwable instanceof DumpAPIException){
        //     return $throwable;
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
            previous: $throwable
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
