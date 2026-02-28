<?php

namespace Kfn\Base\Contracts;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

interface IKfnException
{
    /**
     * Get response code.
     *
     * @return string
     */
    public function getResponseCode(): string;

    /**
     * Get a response message.
     *
     * @return string
     */
    public function getResponseMessage(): string;

    /**
     * @return bool
     */
    public static function shouldRenderException(): bool;

    /**
     * @param  Throwable  $throwable
     * @param  Request  $request
     * @param  Closure|null  $unrenderable
     *
     * @return mixed|void
     * @throws JsonException
     * @throws BindingResolutionException
     */
    public static function renderable(Throwable $throwable, Request $request, Closure|null $unrenderable = null);

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
    ): IResponse|JsonResponse|SymfonyResponse;

    /**
     * @param  Request  $request
     * @param  Throwable  $throwable
     *
     * @return static|Throwable
     */
    public static function mapToException(Request $request, Throwable $throwable): static|Throwable;
}
