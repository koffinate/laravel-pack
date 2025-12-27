<?php

namespace Kfn\UI;

use Kfn\Base\Contracts\IResponseCode;
use Kfn\UI\Contracts\IKfnUiException;

class KfnUiException implements IKfnUiException
{
    public static IResponseCode|null $responseCode = null;
    public static string|null $name = null;
    public static string|null $message = null;
    public static int|null $code = null;
    public static string|null $statusText = null;

    // public function __construct(
    //     IResponseCode|null $responseCode = null,
    //     string|null $name = null,
    //     string|null $message = null,
    //     int|null $code = null,
    //     string|null $statusText = null,
    // ) {
    //     if (is_null(self::$name)) {
    //         self::$responseCode = $responseCode;
    //         self::$name = $name ?? 'already-loaded';
    //         self::$message = $message;
    //         self::$code = $code;
    //         self::$statusText = $statusText;
    //     }
    // }

    public function exist(): bool
    {
        return 'redirect' === config('koffinate.ui.exception.handling_method')
            && self::$responseCode instanceof IResponseCode;
    }

    public function getResponseCode(): IResponseCode|null
    {
        return self::$responseCode;
    }

    public function getName(): string|null
    {
        return self::$name;
    }

    public function getMessage(): string|null
    {
        return self::$message;
    }

    public function getCode(): int|null
    {
        return self::$code;
    }

    public function getStatusText(): string|null
    {
        return self::$statusText;
    }

    public function toArray(): array
    {
        return [
            'sess-id' => session()->id(),
            'source' => self::$responseCode ? self::$responseCode::class : 'unknown',
            'name' => self::$name,
            'status-code' => self::$code,
            'status-text' => self::$statusText,
            'message' => self::$message,
        ];
    }

    // public function __invoke()
    // {
    //     static::of(session('kfn-exception') ?? []);
    // }
}
